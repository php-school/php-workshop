<?php

namespace PhpSchool\PhpWorkshop\ProcessExecutor;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Process\Process;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\Serializer as ResponseSerializer;

/**
 * Class CgiProcessExecutor
 * @package PhpSchool\PhpWorkshop\ProcessExecutor
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 */
class CgiProcessExecutor implements ProcessExecutorInterface, CgiProcessExecutorInterface
{
    /**
     * @var array
     */
    private $env;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @param array            $env
     */
    public function __construct(RequestInterface $request, array $env = [])
    {
        $this->request = $request;
        $this->env    = array_merge([
            'REQUEST_METHOD'  => $request->getMethod(),
            'REDIRECT_STATUS' => 302,
            'QUERY_STRING'    => $request->getUri()->getQuery(),
            'REQUEST_URI'     => $request->getUri()->getPath(),
            'CONTENT_LENGTH'  => $request->getBody()->getSize(),
            'CONTENT_TYPE'    => $request->getHeaderLine('Content-Type')
        ], $env);
    }

    /**
     * Run the given PHP file
     *
     * @param string $fileName
     * @return string
     */
    public function executePhpFile($fileName)
    {
        $cgi = sprintf('php-cgi%s', DIRECTORY_SEPARATOR === '\\' ? '.exe' : '');
        $cgiBinary  = sprintf(
            '%s -dalways_populate_raw_post_data=-1 -dhtml_errors=0 -dexpose_php=0',
            realpath(sprintf('%s/%s', str_replace('\\', '/', dirname(PHP_BINARY)), $cgi))
        );

        $content = $this->request->getBody()->__toString();
        $cmd     = sprintf('echo %s | %s', $content, $cgiBinary);

        $this->env['SCRIPT_FILENAME'] = $fileName;
        foreach ($this->request->getHeaders() as $name => $values) {
            $this->env[sprintf('HTTP_%s', strtoupper($name))] = implode(", ", $values);
        }

        $process = new Process($cmd, null, $this->env);
        $process->run();

        if (!$process->isSuccessful()) {
            throw CodeExecutionException::fromProcess($process);
        }

        //if no status line, pre-pend 200 OK
        $output = $process->getOutput();
        if (!preg_match('/^HTTP\/([1-9]\d*\.\d) ([1-5]\d{2})(\s+(.+))?\\r\\n/', $output)) {
            $output = "HTTP/1.0 200 OK\r\n" . $output;
        }

        return $output;
    }


    /**
     * Run the given PHP file returning a Response object
     *
     * @param $fileName
     * @return Response
     */
    public function asResponseObject($fileName)
    {
        return ResponseSerializer::fromString($this->executePhpFile($fileName));
    }
}
