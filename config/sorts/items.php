<?php

declare(strict_types=1);

/**
 * Item JSON sortField mappings.
 *
 * Maps sort keys to JSONB paths for PostgreSQL sorting.
 * These correspond to the 'sortField' definitions in config/items.php.
 */
return [
    // =====================================================================
    // CORE PROPERTIES (denormalized columns)
    // =====================================================================
    'mass' => ['path' => 'Mass', 'cast' => 'numeric'],

    // =====================================================================
    // CORE PROPERTIES (JSON)
    // =====================================================================
    'dimension.volume_converted' => ['path' => 'InventoryOccupancy.Volume.SCUConverted', 'cast' => 'numeric'],
    'inventory.scu_converted' => ['path' => 'InventoryContainer.SCU', 'cast' => 'numeric'],

    // =====================================================================
    // DURABILITY & DISTORTION
    // =====================================================================
    'durability.health' => ['path' => 'Durability.Health', 'cast' => 'numeric'],
    'distortion.maximum' => ['path' => 'Distortion.Maximum', 'cast' => 'numeric'],
    'distortion.shutdown_time' => ['path' => 'Distortion.ShutdownTime', 'cast' => 'numeric'],
    'distortion.decay_rate' => ['path' => 'Distortion.DecayRate', 'cast' => 'numeric'],
    'distortion.decay_delay' => ['path' => 'Distortion.DecayDelay', 'cast' => 'numeric'],

    // =====================================================================
    // POWER & RESOURCE NETWORK
    // =====================================================================
    'resource_network.usage.power.minimum' => ['path' => 'ResourceNetwork.Usage.Power.Minimum', 'cast' => 'numeric'],
    'resource_network.usage.power.maximum' => ['path' => 'ResourceNetwork.Usage.Power.Maximum', 'cast' => 'numeric'],
    'resource_network.usage.coolant.minimum' => ['path' => 'ResourceNetwork.Usage.Coolant.Minimum', 'cast' => 'numeric'],
    'resource_network.usage.coolant.maximum' => ['path' => 'ResourceNetwork.Usage.Coolant.Maximum', 'cast' => 'numeric'],
    'resource_network.generation.coolant' => ['path' => 'ResourceNetwork.Generation.Coolant', 'cast' => 'numeric'],
    'resource_network.generation.power' => ['path' => 'ResourceNetwork.Generation.Power', 'cast' => 'numeric'],

    // =====================================================================
    // EMISSION & SIGNATURE
    // =====================================================================
    'emission.em_min' => ['path' => 'Emission.Em.Minimum', 'cast' => 'numeric'],
    'emission.em_max' => ['path' => 'Emission.Em.Maximum', 'cast' => 'numeric'],
    'emission.em_per_segment' => ['path' => 'Emission.Em.PerSegment', 'cast' => 'numeric'],
    'emission.ir' => ['path' => 'Emission.Ir', 'cast' => 'numeric'],

    // =====================================================================
    // ARMOR
    // =====================================================================
    'armor.signal_multiplier.cross_section_change' => ['path' => 'Armor.SignalMultipliers.CrossSection', 'cast' => 'numeric'],
    'armor.signal_multiplier.infrared_change' => ['path' => 'Armor.SignalMultipliers.Infrared', 'cast' => 'numeric'],
    'armor.signal_multiplier.electromagnetic_change' => ['path' => 'Armor.SignalMultipliers.Electromagnetic', 'cast' => 'numeric'],

    // =====================================================================
    // BOMB / EXPLOSIVES
    // =====================================================================
    'bomb.damage_total' => ['path' => 'Bomb.DamageTotal', 'cast' => 'numeric'],
    'bomb.explosion.radius_min' => ['path' => 'Bomb.ExplosionMinRadius', 'cast' => 'numeric'],
    'bomb.explosion.radius_max' => ['path' => 'Bomb.ExplosionMaxRadius', 'cast' => 'numeric'],

    // =====================================================================
    // AMMUNITION
    // =====================================================================
    'ammunition.capacity' => ['path' => 'Ammunition.Capacity', 'cast' => 'numeric'],
    'ammunition.speed' => ['path' => 'Ammunition.Speed', 'cast' => 'numeric'],
    'ammunition.range' => ['path' => 'Ammunition.Range', 'cast' => 'numeric'],
    'ammunition.lifetime' => ['path' => 'Ammunition.Lifetime', 'cast' => 'numeric'],
    'ammunition.penetration.base_distance' => ['path' => 'Ammunition.Penetration.BasePenetrationDistance', 'cast' => 'numeric'],
    'ammunition.penetration.near_radius' => ['path' => 'Ammunition.Penetration.NearRadius', 'cast' => 'numeric'],
    'ammunition.penetration.far_radius' => ['path' => 'Ammunition.Penetration.FarRadius', 'cast' => 'numeric'],
    'ammunition.explosion_radius.min' => ['path' => 'Ammunition.ExplosionRadius.Minimum', 'cast' => 'numeric'],
    'ammunition.explosion_radius.max' => ['path' => 'Ammunition.ExplosionRadius.Maximum', 'cast' => 'numeric'],

    // =====================================================================
    // EMP
    // =====================================================================
    'emp.distortion_damage' => ['path' => 'Emp.DistortionDamage', 'cast' => 'numeric'],
    'emp.min_emp_radius' => ['path' => 'Emp.MinEmpRadius', 'cast' => 'numeric'],
    'emp.emp_radius' => ['path' => 'Emp.EmpRadius', 'cast' => 'numeric'],
    'emp.charge_duration' => ['path' => 'Emp.ChargeTime', 'cast' => 'numeric'],
    'emp.unleash_duration' => ['path' => 'Emp.UnleashTime', 'cast' => 'numeric'],
    'emp.cooldown_duration' => ['path' => 'Emp.CooldownTime', 'cast' => 'numeric'],

    // =====================================================================
    // FLIGHT CONTROLLER / IFCS
    // =====================================================================
    'flight_controller.scm_speed' => ['path' => 'Ifcs.ScmSpeed', 'cast' => 'numeric'],
    'flight_controller.max_speed' => ['path' => 'Ifcs.MaxSpeed', 'cast' => 'numeric'],
    'flight_controller.boost_speed_forward' => ['path' => 'Ifcs.BoostSpeedForward', 'cast' => 'numeric'],
    'flight_controller.boost_speed_backward' => ['path' => 'Ifcs.BoostSpeedBackward', 'cast' => 'numeric'],
    'flight_controller.pitch' => ['path' => 'Ifcs.Pitch', 'cast' => 'numeric'],
    'flight_controller.yaw' => ['path' => 'Ifcs.Yaw', 'cast' => 'numeric'],
    'flight_controller.roll' => ['path' => 'Ifcs.Roll', 'cast' => 'numeric'],
    'flight_controller.pitch_boosted' => ['path' => 'Ifcs.PitchBoosted', 'cast' => 'numeric'],
    'flight_controller.yaw_boosted' => ['path' => 'Ifcs.YawBoosted', 'cast' => 'numeric'],
    'flight_controller.roll_boosted' => ['path' => 'Ifcs.RollBoosted', 'cast' => 'numeric'],
    'flight_controller.thruster_decay.linear_accel' => ['path' => 'Ifcs.LinearAccelDecay', 'cast' => 'numeric'],
    'flight_controller.thruster_decay.angular_accel' => ['path' => 'Ifcs.AngularAccelDecay', 'cast' => 'numeric'],
    'flight_controller.multiplier.lift' => ['path' => 'Ifcs.LiftMultiplier', 'cast' => 'numeric'],
    'flight_controller.multiplier.drag' => ['path' => 'Ifcs.DragMultiplier', 'cast' => 'numeric'],
    'flight_controller.multiplier.scm_max_drag' => ['path' => 'Ifcs.ScmMaxDragMultiplier', 'cast' => 'numeric'],
    'flight_controller.multiplier.torque_imbalance' => ['path' => 'Ifcs.TorqueImbalanceMultiplier', 'cast' => 'numeric'],
    'flight_controller.multiplier.precision_landing' => ['path' => 'Ifcs.PrecisionLandingMultiplier', 'cast' => 'numeric'],

    // Afterburner
    'flight_controller.boost_activation.pre_delay_time' => ['path' => 'Ifcs.Afterburner.AfterburnerPreDelayTime', 'cast' => 'numeric'],
    'flight_controller.boost_activation.ramp_up_time' => ['path' => 'Ifcs.Afterburner.AfterburnerRampUpTime', 'cast' => 'numeric'],
    'flight_controller.boost_activation.ramp_down_time' => ['path' => 'Ifcs.Afterburner.AfterburnerRampDownTime', 'cast' => 'numeric'],
    'flight_controller.boost_capacitor.capacity' => ['path' => 'Ifcs.Afterburner.CapacitorMax', 'cast' => 'numeric'],

    // Gravlev
    'flight_controller.gravlev.max_speed' => ['path' => 'Ifcs.Gravlev.HoverMaxSpeed', 'cast' => 'numeric'],
    'flight_controller.gravlev.turn_friction' => ['path' => 'Ifcs.Gravlev.TurnFriction', 'cast' => 'numeric'],
    'flight_controller.gravlev.air_controller_multiplier' => ['path' => 'Ifcs.Gravlev.AirControllerMultiplier', 'cast' => 'numeric'],
    'flight_controller.gravlev.anti_fall_multiplier' => ['path' => 'Ifcs.Gravlev.AntiFallMultiplier', 'cast' => 'numeric'],
    'flight_controller.gravlev.lateral_strafe_multiplier' => ['path' => 'Ifcs.Gravlev.LateralStrafeMultiplier', 'cast' => 'numeric'],

    // =====================================================================
    // JUMP DRIVE
    // =====================================================================
    'jump_drive.alignment_rate' => ['path' => 'JumpDrive.AlignmentRate', 'cast' => 'numeric'],
    'jump_drive.alignment_decay_rate' => ['path' => 'JumpDrive.AlignmentDecayRate', 'cast' => 'numeric'],
    'jump_drive.tuning_rate' => ['path' => 'JumpDrive.TuningRate', 'cast' => 'numeric'],
    'jump_drive.tuning_decay_rate' => ['path' => 'JumpDrive.TuningDecayRate', 'cast' => 'numeric'],
    'jump_drive.fuel_usage_efficiency_multiplier' => ['path' => 'JumpDrive.FuelUsageEfficiencyMultiplier', 'cast' => 'numeric'],

    // =====================================================================
    // QUANTUM DRIVE
    // =====================================================================
    'quantum_drive.fuel_consumption_scu_per_gm' => ['path' => 'QuantumDrive.FuelConsumptionSCUPerGM', 'cast' => 'numeric'],
    'quantum_drive.fuel_efficiency' => ['path' => 'QuantumDrive.FuelEfficiencyGMPerSCU', 'cast' => 'numeric'],
    'quantum_drive.travel_time_10gm.formatted' => ['path' => 'QuantumDrive.TravelTime10GMSeconds', 'cast' => 'numeric'],
    'quantum_drive.standard_jump.spool_up_time' => ['path' => 'QuantumDrive.StandardJump.SpoolUpTime', 'cast' => 'numeric'],
    'quantum_drive.standard_jump.cooldown_time' => ['path' => 'QuantumDrive.StandardJump.CooldownTime', 'cast' => 'numeric'],
    'quantum_drive.standard_jump.interdiction_effect_time' => ['path' => 'QuantumDrive.StandardJump.InterdictionEffectTime', 'cast' => 'numeric'],
    'quantum_drive.standard_jump.calibration_delay_in_seconds' => ['path' => 'QuantumDrive.StandardJump.CalibrationDelayInSeconds', 'cast' => 'numeric'],
    'quantum_drive.standard_jump.drive_speed' => ['path' => 'QuantumDrive.StandardJump.DriveSpeed', 'cast' => 'numeric'],
    'quantum_drive.standard_jump.stage_one_accel_rate' => ['path' => 'QuantumDrive.StandardJump.StageOneAccelRate', 'cast' => 'numeric'],
    'quantum_drive.standard_jump.stage_two_accel_rate' => ['path' => 'QuantumDrive.StandardJump.StageTwoAccelRate', 'cast' => 'numeric'],
    'quantum_drive.spline_jump.drive_speed' => ['path' => 'QuantumDrive.SplineJump.DriveSpeed', 'cast' => 'numeric'],

    // =====================================================================
    // QUANTUM INTERDICTION GENERATOR
    // =====================================================================
    'quantum_interdiction_generator.jammer_range' => ['path' => 'QuantumInterdictionGenerator.JammingRange', 'cast' => 'numeric'],
    'quantum_interdiction_generator.interdiction_range' => ['path' => 'QuantumInterdictionGenerator.InterdictionRange', 'cast' => 'numeric'],
    'quantum_interdiction_generator.charge_duration' => ['path' => 'QuantumInterdictionGenerator.Pulse.ChargeTimeSecs', 'cast' => 'numeric'],
    'quantum_interdiction_generator.activation_duration' => ['path' => 'QuantumInterdictionGenerator.Pulse.ActivationPhaseDurationSeconds', 'cast' => 'numeric'],
    'quantum_interdiction_generator.disperse_charge_duration' => ['path' => 'QuantumInterdictionGenerator.Pulse.DisperseChargeTimeSeconds', 'cast' => 'numeric'],
    'quantum_interdiction_generator.discharge_duration' => ['path' => 'QuantumInterdictionGenerator.Pulse.DischargeTimeSecs', 'cast' => 'numeric'],
    'quantum_interdiction_generator.cooldown_duration' => ['path' => 'QuantumInterdictionGenerator.Pulse.CooldownTimeSecs', 'cast' => 'numeric'],

    // =====================================================================
    // MISSILE & MISSILE RACK
    // =====================================================================
    'missile.signal_type' => ['path' => 'Missile.Targeting.TrackingSignalType', 'cast' => 'text'],
    'missile.tracking_signal_min' => ['path' => 'Missile.Targeting.TrackingSignalMin', 'cast' => 'numeric'],
    'missile.target_lock.signal_resilience_max' => ['path' => 'Missile.Targeting.SignalResilienceMax', 'cast' => 'numeric'],
    'missile.delays.arm_time' => ['path' => 'Missile.ArmTime', 'cast' => 'numeric'],
    'missile.delays.lock_time' => ['path' => 'Missile.Targeting.LockTime', 'cast' => 'numeric'],
    'missile.target_lock.range_min' => ['path' => 'Missile.Targeting.LockRangeMin', 'cast' => 'numeric'],
    'missile.target_lock.range_max' => ['path' => 'Missile.Targeting.LockRangeMax', 'cast' => 'numeric'],
    'missile.target_lock.angle' => ['path' => 'Missile.Targeting.LockingAngle', 'cast' => 'numeric'],
    'missile.target_lock.allow_dumb_firing' => ['path' => 'Missile.Targeting.AllowDumbFiring', 'cast' => 'text'],
    'missile.damage_total' => ['path' => 'Missile.DamageTotal', 'cast' => 'numeric'],
    'missile.explosion.radius_min' => ['path' => 'Missile.ExplosionRadius.Minimum', 'cast' => 'numeric'],
    'missile.explosion.radius_max' => ['path' => 'Missile.ExplosionRadius.Maximum', 'cast' => 'numeric'],
    'missile.flight.speed' => ['path' => 'Missile.GCS.LinearSpeed', 'cast' => 'numeric'],
    'missile.flight.range' => ['path' => 'Missile.Distance', 'cast' => 'numeric'],
    'missile.flight.max_lifetime' => ['path' => 'Missile.MaxLifetime', 'cast' => 'numeric'],
    'missile.flight.boost_phase_duration' => ['path' => 'Missile.GCS.BoostPhaseDuration', 'cast' => 'numeric'],
    'missile.flight.terminal_phase_engagement_time' => ['path' => 'Missile.GCS.TerminalPhaseEngagementTime', 'cast' => 'numeric'],
    'missile.flight.terminal_phase_engagement_angle' => ['path' => 'Missile.GCS.TerminalPhaseEngagementAngle', 'cast' => 'numeric'],
    'missile.flight.intercept_speed' => ['path' => 'Missile.GCS.InterceptSpeed', 'cast' => 'numeric'],
    'missile.flight.terminal_speed' => ['path' => 'Missile.GCS.TerminalSpeed', 'cast' => 'numeric'],
    'missile.flight.boost_speed' => ['path' => 'Missile.GCS.BoostSpeed', 'cast' => 'numeric'],
    'missile_rack.missile_count' => ['path' => 'MissileRack.MissileCount', 'cast' => 'numeric'],
    'missile_rack.missile_size' => ['path' => 'MissileRack.MissileSize', 'cast' => 'numeric'],

    // =====================================================================
    // RADAR
    // =====================================================================
    'radar.sensitivity.infrared' => ['path' => 'Radar.Sensitivity.IR', 'cast' => 'numeric'],
    'radar.sensitivity.cross_section' => ['path' => 'Radar.Sensitivity.CS', 'cast' => 'numeric'],
    'radar.sensitivity.electromagnetic' => ['path' => 'Radar.Sensitivity.EM', 'cast' => 'numeric'],
    'radar.sensitivity.resource' => ['path' => 'Radar.Sensitivity.RS', 'cast' => 'numeric'],
    'radar.sensitivity.db' => ['path' => 'Radar.Sensitivity.dB', 'cast' => 'numeric'],
    'radar.ground_vehicle_sensitivity.infrared' => ['path' => 'Radar.GroundVehicleSensitivity.IR', 'cast' => 'numeric'],
    'radar.ground_vehicle_sensitivity.cross_section' => ['path' => 'Radar.GroundVehicleSensitivity.CS', 'cast' => 'numeric'],
    'radar.ground_vehicle_sensitivity.electromagnetic' => ['path' => 'Radar.GroundVehicleSensitivity.EM', 'cast' => 'numeric'],
    'radar.ground_vehicle_sensitivity.resource' => ['path' => 'Radar.GroundVehicleSensitivity.RS', 'cast' => 'numeric'],
    'radar.ground_vehicle_sensitivity.db' => ['path' => 'Radar.GroundVehicleSensitivity.dB', 'cast' => 'numeric'],
    'radar.piercing.infrared' => ['path' => 'Radar.Piercing.IR', 'cast' => 'numeric'],
    'radar.piercing.cross_section' => ['path' => 'Radar.Piercing.CS', 'cast' => 'numeric'],
    'radar.piercing.electromagnetic' => ['path' => 'Radar.Piercing.EM', 'cast' => 'numeric'],
    'radar.piercing.resource' => ['path' => 'Radar.Piercing.RS', 'cast' => 'numeric'],
    'radar.piercing.db' => ['path' => 'Radar.Piercing.dB', 'cast' => 'numeric'],

    // =====================================================================
    // WEAPON GUN (Hardpoint Weapons)
    // =====================================================================
    'vehicle_weapon.rpm' => ['path' => 'Weapon.RateOfFire', 'cast' => 'numeric'],
    'vehicle_weapon.pellets_per_shot' => ['path' => 'Weapon.PelletsPerShot', 'cast' => 'numeric'],

    // Weapon Damage
    'vehicle_weapon.damage.alpha.physical' => ['path' => 'Weapon.Damage.Alpha.Physical', 'cast' => 'numeric'],
    'vehicle_weapon.damage.alpha.energy' => ['path' => 'Weapon.Damage.Alpha.Energy', 'cast' => 'numeric'],
    'vehicle_weapon.damage.alpha.distortion' => ['path' => 'Weapon.Damage.Alpha.Distortion', 'cast' => 'numeric'],
    'vehicle_weapon.damage.alpha.stun' => ['path' => 'Weapon.Damage.Alpha.Stun', 'cast' => 'numeric'],
    'vehicle_weapon.damage.alpha_total' => ['path' => 'Weapon.Damage.AlphaTotal', 'cast' => 'numeric'],
    'vehicle_weapon.damage.maximum' => ['path' => 'Weapon.Damage.Maximum', 'cast' => 'text'],
    'vehicle_weapon.damage.dps.physical' => ['path' => 'Weapon.Damage.Dps.Physical', 'cast' => 'numeric'],
    'vehicle_weapon.damage.dps.energy' => ['path' => 'Weapon.Damage.Dps.Energy', 'cast' => 'numeric'],
    'vehicle_weapon.damage.dps.distortion' => ['path' => 'Weapon.Damage.Dps.Distortion', 'cast' => 'numeric'],
    'vehicle_weapon.damage.dps.stun' => ['path' => 'Weapon.Damage.Dps.Stun', 'cast' => 'numeric'],
    'vehicle_weapon.damage.dps_total' => ['path' => 'Weapon.Damage.DpsTotal', 'cast' => 'numeric'],
    'vehicle_weapon.damage.burst' => ['path' => 'Weapon.Damage.Burst', 'cast' => 'numeric'],
    'vehicle_weapon.damage.max_per_mag' => ['path' => 'Weapon.Damage.MaxPerMag', 'cast' => 'numeric'],
    'vehicle_weapon.damage.sustained_60s' => ['path' => 'Weapon.Damage.Sustained60s', 'cast' => 'numeric'],

    // Weapon Heat
    'vehicle_weapon.heat.per_shot' => ['path' => 'Weapon.Heat.HeatPerShot', 'cast' => 'numeric'],
    'vehicle_weapon.heat.overheat_max_time' => ['path' => 'Weapon.Heat.TimeToOverheat', 'cast' => 'numeric'],
    'vehicle_weapon.heat.overheat_max_shots' => ['path' => 'Weapon.Heat.ShotsToOverheat', 'cast' => 'numeric'],
    'vehicle_weapon.heat.overheat_cooldown' => ['path' => 'Weapon.Heat.OverheatFixTime', 'cast' => 'numeric'],
    'vehicle_weapon.heat.cooling_delay' => ['path' => 'Weapon.Heat.CoolingDelay', 'cast' => 'numeric'],
    'vehicle_weapon.heat.cooling_per_second' => ['path' => 'Weapon.Heat.CoolingPerSecond', 'cast' => 'numeric'],

    // Weapon Spread
    'vehicle_weapon.spread.minimum' => ['path' => 'Weapon.Spread.Minimum', 'cast' => 'numeric'],
    'vehicle_weapon.spread.maximum' => ['path' => 'Weapon.Spread.Maximum', 'cast' => 'numeric'],
    'vehicle_weapon.spread.first_attack' => ['path' => 'Weapon.Spread.FirstAttack', 'cast' => 'numeric'],
    'vehicle_weapon.spread.per_attack' => ['path' => 'Weapon.Spread.Attack', 'cast' => 'numeric'],

    // Weapon Charge
    'vehicle_weapon.charge.time' => ['path' => 'Weapon.Charge.ChargeTime', 'cast' => 'numeric'],
    'vehicle_weapon.charge.overcharge' => ['path' => 'Weapon.Charge.OverchargeTime', 'cast' => 'numeric'],
    'vehicle_weapon.charge.overcharged' => ['path' => 'Weapon.Charge.OverchargedTime', 'cast' => 'numeric'],
    'vehicle_weapon.charge.cooldown' => ['path' => 'Weapon.Charge.CooldownTime', 'cast' => 'numeric'],

    // Weapon Capacitor
    'vehicle_weapon.capacitor.max_ammo_load' => ['path' => 'Weapon.Capacitor.MaxAmmoLoad', 'cast' => 'numeric'],
    'vehicle_weapon.capacitor.regen_per_sec' => ['path' => 'Weapon.Capacitor.MaxRegenPerSec', 'cast' => 'numeric'],
    'vehicle_weapon.capacitor.cooldown_time' => ['path' => 'Weapon.Capacitor.Cooldown', 'cast' => 'numeric'],

    // Weapon Charge Modifier
    'vehicle_weapon.charge_modifier.damage' => ['path' => 'Weapon.ChargeModifier.Damage', 'cast' => 'numeric'],
    'vehicle_weapon.charge_modifier.ammo_speed' => ['path' => 'Weapon.ChargeModifier.AmmoSpeed', 'cast' => 'numeric'],
    'vehicle_weapon.charge_modifier.fire_rate' => ['path' => 'Weapon.ChargeModifier.FireRate', 'cast' => 'numeric'],

    // =====================================================================
    // WEAPONS PERSONAL (FPS Personal)
    // =====================================================================
    'personal_weapon.rpm' => ['path' => 'Weapon.RateOfFire', 'cast' => 'numeric'],
    'personal_weapon.pellets_per_shot' => ['path' => 'Weapon.PelletsPerShot', 'cast' => 'numeric'],

    // Weapon Damage
    'personal_weapon.damage.alpha.physical' => ['path' => 'Weapon.Damage.Alpha.Physical', 'cast' => 'numeric'],
    'personal_weapon.damage.alpha.energy' => ['path' => 'Weapon.Damage.Alpha.Energy', 'cast' => 'numeric'],
    'personal_weapon.damage.alpha.distortion' => ['path' => 'Weapon.Damage.Alpha.Distortion', 'cast' => 'numeric'],
    'personal_weapon.damage.alpha.stun' => ['path' => 'Weapon.Damage.Alpha.Stun', 'cast' => 'numeric'],
    'personal_weapon.damage.alpha_total' => ['path' => 'Weapon.Damage.AlphaTotal', 'cast' => 'numeric'],
    'personal_weapon.damage.dps.physical' => ['path' => 'Weapon.Damage.Dps.Physical', 'cast' => 'numeric'],
    'personal_weapon.damage.dps.energy' => ['path' => 'Weapon.Damage.Dps.Energy', 'cast' => 'numeric'],
    'personal_weapon.damage.dps.distortion' => ['path' => 'Weapon.Damage.Dps.Distortion', 'cast' => 'numeric'],
    'personal_weapon.damage.dps.stun' => ['path' => 'Weapon.Damage.Dps.Stun', 'cast' => 'numeric'],
    'personal_weapon.damage.dps_total' => ['path' => 'Weapon.Damage.DpsTotal', 'cast' => 'numeric'],
    'personal_weapon.damage.burst' => ['path' => 'Weapon.Damage.Burst', 'cast' => 'numeric'],
    'personal_weapon.damage.maximum' => ['path' => 'Weapon.Damage.MaxPerMag', 'cast' => 'numeric'],

    // Weapon Spread
    'personal_weapon.spread.minimum' => ['path' => 'Weapon.Spread.Minimum', 'cast' => 'numeric'],
    'personal_weapon.spread.maximum' => ['path' => 'Weapon.Spread.Maximum', 'cast' => 'numeric'],
    'personal_weapon.spread.first_attack' => ['path' => 'Weapon.Spread.FirstAttack', 'cast' => 'numeric'],
    'personal_weapon.spread.per_attack' => ['path' => 'Weapon.Spread.Attack', 'cast' => 'numeric'],
    'personal_weapon.ads_spread.minimum' => ['path' => 'Weapon.AdsSpread.Minimum', 'cast' => 'numeric'],
    'personal_weapon.ads_spread.maximum' => ['path' => 'Weapon.AdsSpread.Maximum', 'cast' => 'numeric'],
    'personal_weapon.ads_spread.first_attack' => ['path' => 'Weapon.AdsSpread.FirstAttack', 'cast' => 'numeric'],
    'personal_weapon.ads_spread.per_attack' => ['path' => 'Weapon.AdsSpread.Attack', 'cast' => 'numeric'],

    // Weapon Charge
    'personal_weapon.charge.charge_duration' => ['path' => 'Weapon.Charge.ChargeTime', 'cast' => 'numeric'],
    'personal_weapon.charge.overcharge_time' => ['path' => 'Weapon.Charge.OverchargeTime', 'cast' => 'numeric'],
    'personal_weapon.charge.overcharged_duration' => ['path' => 'Weapon.Charge.OverchargedTime', 'cast' => 'numeric'],
    'personal_weapon.charge.cooldown_time' => ['path' => 'Weapon.Charge.CooldownTime', 'cast' => 'numeric'],

    // Weapon Charge Modifier
    'personal_weapon.charge_modifier.damage' => ['path' => 'Weapon.ChargeModifier.Damage', 'cast' => 'numeric'],
    'personal_weapon.charge_modifier.ammo_speed' => ['path' => 'Weapon.ChargeModifier.AmmoSpeed', 'cast' => 'numeric'],
    'personal_weapon.charge_modifier.fire_rate' => ['path' => 'Weapon.ChargeModifier.FireRate', 'cast' => 'numeric'],

    // =====================================================================
    // WEAPON DEFENSIVE
    // =====================================================================
    'counter_measure.type' => ['path' => 'WeaponDefensive.Type', 'cast' => 'text'],
    'counter_measure.signature.cross_section' => ['path' => 'WeaponDefensive.Signatures.CrossSection.End', 'cast' => 'numeric'],
    'counter_measure.signature.infrared' => ['path' => 'WeaponDefensive.Signatures.Infrared.End', 'cast' => 'numeric'],
    'counter_measure.signature.electromagnetic' => ['path' => 'WeaponDefensive.Signatures.Electromagnetic.End', 'cast' => 'numeric'],
    'counter_measure.signature.decibel' => ['path' => 'WeaponDefensive.Signatures.Decibel.End', 'cast' => 'numeric'],

    // =====================================================================
    // WEAPON MODIFIER
    // =====================================================================
    'weapon_modifier.base.damage_change' => ['path' => 'WeaponModifier.WeaponStats.Base.DamageMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.base.projectile_speed_change' => ['path' => 'WeaponModifier.WeaponStats.Base.ProjectileSpeedMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.base.ammo_cost_change' => ['path' => 'WeaponModifier.WeaponStats.Base.AmmoCostMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.base.sound_radius_change' => ['path' => 'WeaponModifier.WeaponStats.Base.SoundRadiusMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.base.muzzle_flash_change' => ['path' => 'WeaponModifier.WeaponStats.Base.MuzzleFlashScale', 'cast' => 'numeric'],
    'weapon_modifier.base.heat_generation_change' => ['path' => 'WeaponModifier.WeaponStats.Base.HeatGenerationMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.recoil.multiplier_change' => ['path' => 'WeaponModifier.WeaponStats.Recoil.RandomnessMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.recoil.decay_change' => ['path' => 'WeaponModifier.WeaponStats.Recoil.DecayMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.spread.min_change' => ['path' => 'WeaponModifier.WeaponStats.Spread.MinMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.spread.max_change' => ['path' => 'WeaponModifier.WeaponStats.Spread.MaxMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.spread.first_attack_change' => ['path' => 'WeaponModifier.WeaponStats.Spread.FirstAttackMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.spread.per_attack_change' => ['path' => 'WeaponModifier.WeaponStats.Spread.AttackMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.spread.decay_change' => ['path' => 'WeaponModifier.WeaponStats.Spread.DecayMultiplier', 'cast' => 'numeric'],
    'weapon_modifier.aim.second_zoom_scale' => ['path' => 'WeaponModifier.WeaponStats.Aim.SecondZoomScale', 'cast' => 'numeric'],

    // =====================================================================
    // WEAPON ATTACHMENT - IRON SIGHT
    // =====================================================================
    'iron_sight.default_range' => ['path' => 'WeaponAttachment.IronSight.DefaultRange', 'cast' => 'numeric'],
    'iron_sight.max_range' => ['path' => 'WeaponAttachment.IronSight.MaxRange', 'cast' => 'numeric'],
    'iron_sight.range_increment' => ['path' => 'WeaponAttachment.IronSight.RangeIncrement', 'cast' => 'numeric'],
    'iron_sight.auto_zeroing_time' => ['path' => 'WeaponAttachment.IronSight.AutoZeroingTime', 'cast' => 'numeric'],
    'iron_sight.zoom_scale' => ['path' => 'WeaponAttachment.IronSight.ZoomScale', 'cast' => 'numeric'],
    'iron_sight.zoom_time_change' => ['path' => 'WeaponAttachment.IronSight.ZoomTimeScale', 'cast' => 'numeric'],

    // =====================================================================
    // WEAPON ATTACHMENT - LASER POINTER
    // =====================================================================
    'laser_pointer.range' => ['path' => 'WeaponAttachment.LaserPointer.Range', 'cast' => 'numeric'],

    // =====================================================================
    // SHIELD
    // =====================================================================
    'shield.max_health' => ['path' => 'Shield.MaxShieldHealth', 'cast' => 'numeric'],
    'shield.regen_rate' => ['path' => 'Shield.MaxShieldRegen', 'cast' => 'numeric'],
    'shield.regen_time' => ['path' => 'Shield.RegenerationTime', 'cast' => 'numeric'],
    'shield.regen_delay.damage' => ['path' => 'Shield.DamagedDelay', 'cast' => 'numeric'],
    'shield.regen_delay.downed' => ['path' => 'Shield.DownedDelay', 'cast' => 'numeric'],
    'shield.reserve_pool.regen_rate' => ['path' => 'Shield.ReservePool.MaxShieldRegen', 'cast' => 'numeric'],
    'shield.reserve_pool.regen_time' => ['path' => 'Shield.ReservePool.RegenerationTime', 'cast' => 'numeric'],

    // =====================================================================
    // SHIELD CONTROLLER
    // =====================================================================
    'shield_controller.face_type' => ['path' => 'ShieldController.FaceType', 'cast' => 'text'],
    'shield_controller.max_reallocation' => ['path' => 'ShieldController.MaxReallocation', 'cast' => 'numeric'],
    'shield_controller.reconfiguration_cooldown' => ['path' => 'ShieldController.ReconfigurationCooldown', 'cast' => 'numeric'],
    'shield_controller.max_electrical_charge_damage_rate' => ['path' => 'ShieldController.MaxElectricalChargeDamageRate', 'cast' => 'numeric'],

    // =====================================================================
    // MINING LASER
    // =====================================================================
    'mining_laser.optimal_range' => ['path' => 'MiningLaser.OptimalRange', 'cast' => 'numeric'],
    'mining_laser.maximum_range' => ['path' => 'MiningLaser.MaximumRange', 'cast' => 'numeric'],
    'mining_laser.power_transfer' => ['path' => 'MiningLaser.PowerTransfer', 'cast' => 'numeric'],
    'mining_laser.min_power_transfer' => ['path' => 'MiningLaser.MinPowerTransfer', 'cast' => 'numeric'],
    'mining_laser.throttle_minimum' => ['path' => 'MiningLaser.ThrottleMinimum', 'cast' => 'numeric'],
    'mining_laser.throttle_lerp_speed' => ['path' => 'MiningLaser.ThrottleLerpSpeed', 'cast' => 'numeric'],
    'mining_laser.module_slots' => ['path' => 'MiningLaser.ModuleSlots', 'cast' => 'numeric'],
    'mining_laser.laser_power.minimum' => ['path' => 'MiningLaser.MinPowerTransfer', 'cast' => 'numeric'],
    'mining_laser.laser_power.maximum' => ['path' => 'MiningLaser.PowerTransfer', 'cast' => 'numeric'],
    'mining_laser.modifier_map.resistance' => ['path' => 'MiningLaser.Modifiers.Resistance', 'cast' => 'numeric'],
    'mining_laser.modifier_map.laser_instability' => ['path' => 'MiningLaser.Modifiers.Instability', 'cast' => 'numeric'],
    'mining_laser.modifier_map.optimal_charge_window_size' => ['path' => 'MiningLaser.Modifiers.OptimalChargeWindow', 'cast' => 'numeric'],
    'mining_laser.modifier_map.optimal_charge_rate' => ['path' => 'MiningLaser.Modifiers.OptimalChargeRate', 'cast' => 'numeric'],
    'mining_laser.modifier_map.inert_materials' => ['path' => 'MiningLaser.Modifiers.InertMaterials', 'cast' => 'numeric'],

    // =====================================================================
    // MINING MODULE
    // =====================================================================
    'mining_modifier.type' => ['path' => 'MiningModule.Type', 'cast' => 'text'],
    'mining_modifier.charges' => ['path' => 'MiningModule.Charges', 'cast' => 'numeric'],
    'mining_modifier.duration' => ['path' => 'MiningModule.Lifetime', 'cast' => 'numeric'],
    'mining_modifier.power_modifier' => ['path' => 'MiningModule.Modifiers.DamageMultiplierChange', 'cast' => 'numeric'],
    'mining_modifier.modifier_map.resistance' => ['path' => 'MiningModule.Modifiers.Resistance', 'cast' => 'numeric'],
    'mining_modifier.modifier_map.laser_instability' => ['path' => 'MiningModule.Modifiers.Instability', 'cast' => 'numeric'],
    'mining_modifier.modifier_map.optimal_charge_window_size' => ['path' => 'MiningModule.Modifiers.OptimalChargeWindow', 'cast' => 'numeric'],
    'mining_modifier.modifier_map.optimal_charge_rate' => ['path' => 'MiningModule.Modifiers.OptimalChargeRate', 'cast' => 'numeric'],
    'mining_modifier.modifier_map.shatter_damage' => ['path' => 'MiningModule.Modifiers.ShatterDamage', 'cast' => 'numeric'],
    'mining_modifier.modifier_map.cluster_factor' => ['path' => 'MiningModule.Modifiers.ClusterFactor', 'cast' => 'numeric'],
    'mining_modifier.modifier_map.overcharge_rate' => ['path' => 'MiningModule.Modifiers.OverchargeRate', 'cast' => 'numeric'],
    'mining_modifier.modifier_map.inert_materials' => ['path' => 'MiningModule.Modifiers.InertMaterials', 'cast' => 'numeric'],

    // =====================================================================
    // TRACTOR BEAM
    // =====================================================================
    'tractor_beam.range.min' => ['path' => 'TractorBeam.MinDistance', 'cast' => 'numeric'],
    'tractor_beam.range.max' => ['path' => 'TractorBeam.MaxDistance', 'cast' => 'numeric'],
    'tractor_beam.range.max_angle' => ['path' => 'TractorBeam.MaxAngle', 'cast' => 'numeric'],
    'tractor_beam.range.max_volume' => ['path' => 'TractorBeam.MaxVolume', 'cast' => 'numeric'],
    'tractor_beam.range.full_strength_distance' => ['path' => 'TractorBeam.FullStrengthDistance', 'cast' => 'numeric'],
    'tractor_beam.force.min' => ['path' => 'TractorBeam.MinForce', 'cast' => 'numeric'],
    'tractor_beam.force.max' => ['path' => 'TractorBeam.MaxForce', 'cast' => 'numeric'],
    'tractor_beam.towing.force' => ['path' => 'TractorBeam.Towing.TowingForce', 'cast' => 'numeric'],
    'tractor_beam.towing.max_distance' => ['path' => 'TractorBeam.Towing.TowingMaxDistance', 'cast' => 'numeric'],
    'tractor_beam.towing.max_acceleration' => ['path' => 'TractorBeam.Towing.TowingMaxAcceleration', 'cast' => 'numeric'],
    'tractor_beam.towing.qt_mass_limit' => ['path' => 'TractorBeam.Towing.QuantumTowMassLimit', 'cast' => 'numeric'],

    // =====================================================================
    // SALVAGE MODIFIER
    // =====================================================================
    'salvage_modifier.salvage_speed_multiplier' => ['path' => 'SalvageModifier.SalvageSpeedMultiplier', 'cast' => 'numeric'],
    'salvage_modifier.radius_multiplier' => ['path' => 'SalvageModifier.RadiusMultiplier', 'cast' => 'numeric'],
    'salvage_modifier.extraction_efficiency' => ['path' => 'SalvageModifier.ExtractionEfficiency', 'cast' => 'numeric'],

    // =====================================================================
    // SELF DESTRUCT
    // =====================================================================
    'self_destruct.damage' => ['path' => 'SelfDestruct.Damage', 'cast' => 'numeric'],
    'self_destruct.countdown' => ['path' => 'SelfDestruct.Time', 'cast' => 'numeric'],
    'self_destruct.min_radius' => ['path' => 'SelfDestruct.MinRadius', 'cast' => 'numeric'],
    'self_destruct.phys_radius' => ['path' => 'SelfDestruct.PhysRadius', 'cast' => 'numeric'],

    // =====================================================================
    // COOLDOWN (General)
    // =====================================================================
    'cooldown' => ['path' => 'Cooldown', 'cast' => 'numeric'],

    // =====================================================================
    // CLOTHING & ARMOR
    // =====================================================================
    'temperature_resistance.minimum' => ['path' => 'TemperatureResistance.Minimum', 'cast' => 'numeric'],
    'temperature_resistance.maximum' => ['path' => 'TemperatureResistance.Maximum', 'cast' => 'numeric'],
    'clothing.radiation_resistance.maximum_radiation_capacity' => ['path' => 'RadiationResistance.MaximumRadiationCapacity', 'cast' => 'numeric'],
    'clothing.radiation_resistance.radiation_dissipation_rate' => ['path' => 'RadiationResistance.RadiationDissipationRate', 'cast' => 'numeric'],

    // =====================================================================
    // SUIT ARMOR
    // =====================================================================
    'clothing.damage_resistance_map.physical_change' => ['path' => 'SuitArmor.DamageResistance.Physical.Multiplier', 'cast' => 'numeric'],
    'clothing.damage_resistance_map.energy_change' => ['path' => 'SuitArmor.DamageResistance.Energy.Multiplier', 'cast' => 'numeric'],
    'clothing.damage_resistance_map.distortion_change' => ['path' => 'SuitArmor.DamageResistance.Distortion.Multiplier', 'cast' => 'numeric'],
    'clothing.damage_resistance_map.thermal_change' => ['path' => 'SuitArmor.DamageResistance.Thermal.Multiplier', 'cast' => 'numeric'],
    'clothing.damage_resistance_map.stun_change' => ['path' => 'SuitArmor.DamageResistance.Stun.Multiplier', 'cast' => 'numeric'],
    'clothing.damage_resistance_map.impact_change' => ['path' => 'SuitArmor.DamageResistance.Impact', 'cast' => 'numeric'],
    'clothing.signature.electromagnetic' => ['path' => 'SuitArmor.Signature.Electromagnetic', 'cast' => 'numeric'],
    'clothing.signature.infrared' => ['path' => 'SuitArmor.Signature.Infrared', 'cast' => 'numeric'],

    // =====================================================================
    // FOOD & NUTRITION
    // =====================================================================
    'food.nutrition.hunger' => ['path' => 'Food.Nutrition.Hunger.Total', 'cast' => 'numeric'],
    'food.nutrition.thirst' => ['path' => 'Food.Nutrition.Thirst.Total', 'cast' => 'numeric'],
    'food.nutrition.blood_drug_level' => ['path' => 'Food.Nutrition.BloodDrugLevel.Total', 'cast' => 'numeric'],

];
