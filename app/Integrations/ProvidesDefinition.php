<?php

namespace App\Integrations;

interface ProvidesDefinition
{
    public static function definition(): ProviderDefinition;
}
