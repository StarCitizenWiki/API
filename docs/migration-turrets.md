# Migrate from legacy turret summaries to mounting positions

Use `GET /api/v3/vehicles/{uuid}` (or the equivalent unversioned `GET /api/vehicles/{uuid}`, including its `/api/ground-vehicles/{uuid}` and `/api/gravlev-vehicles/{uuid}` aliases) to read `data.mounting_positions`. The list endpoint does not include it. Each row can stand on its own for a mount overview: it includes the hardpoint name, occupancy, size limits, and payload. It also includes installed size, classification, known control routes, and combat stats when available. Clients that only render the loadout can keep reading `data.ports` as before.

1. Check whether `data.mounting_positions` is present. If it is absent, the snapshot predates this field. Fall back to `data.turrets.manned`, `data.turrets.remote`, and `data.turrets.pdc` if you need turret summaries. An empty list means the snapshot has no eligible positions, so do not fall back in that case.
2. For a turret view, select rows with `mount_type: Turret`. Use `turret_role` for the role (`Manned`, `Remote`, `Pdc`, or `Autonomous`) and `control_routes` for known operators. An autonomous turret can have a crew route. `is_pilot_slaveable` is a separate flag, not proof of a pilot route.
3. Use `payload` for the installed equipment carried by a position. A gimbal or missile rack and its child equipment are one position, not separate positions. Use `occupied` to distinguish vacant positions; a vacant position has no installed `mount_type`. `min_size` and `max_size` describe port capacity, not the installed item's `installed_size`.
4. Join to `data.ports` only if you need details such as a localized label, the installed assembly's identity, or full port compatibility. Index the root and nested `ports` by `port_id`, then look up the position's `port_id`. Both are in the same response; no extra request is needed. Do not join by hardpoint name or reuse IDs across snapshots. On v3 and unversioned detail responses, every port with a `port_id` also carries `port_display_name`, `part_display_name`, `part_name`, and `installed_size` when the dump provides them. `port_display_name` is the localized port label, including nested adapter and payload ports; `part_display_name` and `part_name` identify the hull part hosting the port. `installed_size` is the installed item's declared size, including nested adapters such as gimbal mounts, and needs no item database lookup. An adapter's `installed_size` can exceed the port's `sizes.max`.
5. `mounting_positions` does not carry the legacy turret facts: there are no `legacy_bucket`, `legacy_ordinal`, or `legacy_size` fields. Read the unchanged `turrets.manned`, `turrets.remote`, and `turrets.pdc` responses for exact legacy values, including bucket membership, row order, sizes, and labels. The fields `mounting_positions` does carry describe the current state: `mount_type`/`turret_role` reflect the installed item, so a vacant position has neither, and the legacy buckets also contained vacant positions. `installed_size`, `min_size`, and `max_size` describe the current item and port capacity, which can differ from the legacy size (an adapter's `installed_size` can even exceed `sizes.max`).

On some routes, `control_routes[].control` describes the game-data binding, not the player's available actions. `Aim` means a weapon controller targets the turret. `Payload` means a salvage controller targets the tractor beam. Neither value proves that the operator can only aim or can only operate the tool. For a player-facing view, show the operator, route kind, and seat label without using this field to describe gameplay restrictions.

`mounting_positions` covers more than turrets. It includes fixed guns, gimbals, missile racks, and utility tools. Do not count all positions as turrets or treat an unknown role as `Remote`.

## Deprecation markers

`turrets.manned`, `turrets.remote`, and `turrets.pdc` are marked deprecated in the OpenAPI schema. Their responses are unchanged. On v3 and unversioned detail responses that include `mounting_positions` (including an empty list), the same three field paths are listed in `meta.deprecated_fields` with the migration hint. Older snapshots without `mounting_positions`, v2 responses, and index responses keep serving the legacy summaries without the notices. The v2 responses keep the legacy `hardpoints` shape.

## Example: List manned turrets without a join

For a basic overview, read the position and payload directly. This example assumes `mounting_positions` is present. Use the legacy fallback above if it is absent.

```js
const mannedTurrets = data.mounting_positions
  .filter(position => position.mount_type === 'Turret' && position.turret_role === 'Manned')
  .map(position => ({
    hardpoint: position.hardpoint_name,
    installedSize: position.installed_size,
    payload: position.payload,
  }));
```

Payload names describe what the turret carries, not the turret assembly itself.

## Example: Add display labels with an optional join

This version looks up the linked port for a friendlier label and keeps the old display working for snapshots without `mounting_positions`:

```js
function mannedTurretsForDisplay({ data }) {
  if (!Object.hasOwn(data, 'mounting_positions')) {
    return (data.turrets?.manned ?? []).map(turret => ({
      name: turret.display_name ?? turret.hardpoint_name,
      hardpoint: turret.hardpoint_name,
    }));
  }

  const portsById = new Map();
  const pending = [...data.ports];
  while (pending.length > 0) {
    const port = pending.pop();
    if (port.port_id != null) portsById.set(port.port_id, port);
    pending.push(...(port.ports ?? []));
  }

  return data.mounting_positions
    .filter(position => position.mount_type === 'Turret' && position.turret_role === 'Manned')
    .map(position => {
      const port = portsById.get(position.port_id);
      return {
        name: port?.port_display_name ?? port?.part_display_name ?? port?.class_name ?? position.hardpoint_name,
        hardpoint: position.hardpoint_name,
      };
    });
}
```

This example builds display rows. It does not reproduce legacy bucket ordering or counts.

## Exact legacy values

The legacy `turrets.*` responses are still returned unchanged, including their original row order, membership, sizes, labels, and per-mount details. `mounting_positions` cannot reproduce them exactly: it makes no ordering promise, it carries no legacy fields, and its current classification and size fields can legitimately differ from the legacy facts (vacant positions, adapters whose `installed_size` exceeds port capacity, and role reclassifications). Do not try to reconstruct legacy rows from `mounting_positions`; read `turrets.*` for that. Earlier snapshots may have `mounting_positions` while `turrets.*` keeps serving the same values either way.

Filter by `mount_type`/`turret_role` only for a current-classification view; the legacy buckets also contain vacant positions that have no mount type. Do not sum per-position `combat_stats` to replace aggregate `weaponry` totals.
