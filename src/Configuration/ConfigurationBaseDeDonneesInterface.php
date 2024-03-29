<?php

namespace App\Trellotrolle\Configuration;

interface ConfigurationBaseDeDonneesInterface
{
    public function getLogin(): string;

    public function getMotDePasse(): string;

    public function getDSN(): string;

    public function getOptions(): array;
}