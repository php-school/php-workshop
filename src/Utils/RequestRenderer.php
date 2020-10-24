<?php

namespace PhpSchool\PhpWorkshop\Utils;

use Psr\Http\Message\RequestInterface;

/**
 * Utility to render a PSR-7 request
 */
class RequestRenderer
{
    /**
     * Render a PSR-7 request.
     *
     * @param RequestInterface $request
     * @return string
     */
    public function renderRequest(RequestInterface $request): string
    {
        $return  = '';
        $return .= sprintf("URL:     %s\n", $request->getUri());
        $return .= sprintf("METHOD:  %s\n", $request->getMethod());

        if ($request->getHeaders()) {
            $return .= 'HEADERS:';
        }

        $indent = false;
        foreach ($request->getHeaders() as $name => $values) {
            if ($indent) {
                $return .= str_repeat(' ', 8);
            }

            $return .= sprintf(" %s: %s\n", $name, implode(', ', $values));
            $indent  = true;
        }

        if ($body = (string) $request->getBody()) {
            $return .= 'BODY:    ';

            switch ($request->getHeaderLine('Content-Type')) {
                case 'application/json':
                    $return .= json_encode(json_decode($body, true), JSON_PRETTY_PRINT);
                    break;
                default:
                    $return .= $body;
                    break;
            }

            $return .= "\n";
        }

        return $return;
    }
}
