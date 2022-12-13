<?php

namespace PhpSchool\PhpWorkshop\UserState;

interface Serializer
{
    public function serialize(UserState $state): void;

    public function deSerialize(): UserState;
}
