<?php

namespace Labudzinski\TestFrameworkBundle\Test\DataFixtures;

interface AliceFixtureLoaderAwareInterface
{
    /**
     * @param AliceFixtureLoader $loader
     */
    public function setLoader(AliceFixtureLoader $loader);
}
