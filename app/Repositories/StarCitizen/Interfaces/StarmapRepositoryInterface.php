<?php declare(strict_types = 1);

namespace App\Repositories\StarCitizen\Interfaces;

/**
 * Interface StarmapInterface
 * @package App\Repositories\StarCitizen\Interfaces
 */
interface StarmapRepositoryInterface
{
    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}
     *
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function getSystem(string $systemName);

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/asteroidbelts
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function getAsteroidbelts(string $systemName);

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/spacestations
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function getSpacestations(string $systemName);

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/jumppoints
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function getJumppoints(string $systemName);

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/planets
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function getPlanets(string $systemName);

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/moons
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function getMoons(string $systemName);

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/landingzones
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function getLandingzones(string $systemName);

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/stars
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function getStars(string $systemName);

    /**
     * https://robertsspaceindustries.com/api/starmap/celestial-objects/{SYSTEM_NAME}.[TYPE}.{NAME}
     *
     * @param string $objectName
     *
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function getCelestialObject(string $objectName);

    /**
     * https://robertsspaceindustries.com/api/starmap/find
     * POST Parameter: query
     *
     * @param string $searchString
     *
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function search(string $searchString);

    /**
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function getSystemList();

    /**
     * @return \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface
     */
    public function getCelestialObjectList();
}
