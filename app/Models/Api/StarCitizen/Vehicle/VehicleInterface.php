<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 21:25
 */

namespace App\Models\Api\StarCitizen\Vehicle;

/**
 * Interface VehicleInterface
 */
interface VehicleInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function description();

    public function translations();

    /**
     * The Vehicle Foci
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function foci();

    /**
     * The Vehicle Manufacturer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manufacturer();

    /**
     * The Vehicle Production Status
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productionStatus();

    /**
     * The Vehicle Production Note
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productionNote();

    /**
     * The Vehicle Role Type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type();

    /**
     * The Vehicle Size
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function size();
}
