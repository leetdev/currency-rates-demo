<?php

namespace Ultraleet\CurrencyRates;

use Illuminate\Http\Request;
use Ultraleet\CurrencyRates\Contracts\Provider as ProviderContract;

abstract class AbstractProvider implements ProviderContract
{
    /**
     * The HTTP request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new provider instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->init();
    }

    /**
     * Perform provider specific initialization.
     *
     * @return void
     */
    public function init()
    {

    }
}
