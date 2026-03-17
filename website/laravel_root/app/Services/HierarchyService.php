<?php

namespace App\Services;

use App\Models\Domain\OrganisationUnit;
use App\Models\Relations\OrganisationUnitClosure;
use Illuminate\Support\Facades\DB;

class HierarchyService
{
    /**
     * Rebuild the closure table for a specific organisation unit when it is created or moved.
     */
    public function updateClosure(OrganisationUnit $organisationUnit): void
    {
        DB::transaction(function () use ($organisationUnit) {
            // 1. Remove existing closure rows for this organisation unit's descendants where ancestor is NOT this organisation unit's subtree
            OrganisationUnitClosure::whereIn('descendant_id', function ($query) use ($organisationUnit) {
                $query->select('descendant_id')
                    ->from('organisation_unit_closure')
                    ->where('ancestor_id', $organisationUnit->id);
            })
            ->whereNotIn('ancestor_id', function ($query) use ($organisationUnit) {
                $query->select('descendant_id')
                    ->from('organisation_unit_closure')
                    ->where('ancestor_id', $organisationUnit->id);
            })
            ->delete();

            // 2. Insert new closure rows
            if ($organisationUnit->parent_id) {
                DB::statement("
                    INSERT INTO organisation_unit_closure (ancestor_id, descendant_id, depth)
                    SELECT super.ancestor_id, sub.descendant_id, super.depth + sub.depth + 1
                    FROM organisation_unit_closure AS super
                    JOIN organisation_unit_closure AS sub
                    WHERE super.descendant_id = ?
                    AND sub.ancestor_id = ?
                ", [$organisationUnit->parent_id, $organisationUnit->id]);
            }
        });
    }

    /**
     * Initialize closure for a new organisation unit.
     */
    public function setInitialClosure(OrganisationUnit $organisationUnit): void
    {
        DB::transaction(function () use ($organisationUnit) {
            // Self-reference
            OrganisationUnitClosure::create([
                'ancestor_id' => $organisationUnit->id,
                'descendant_id' => $organisationUnit->id,
                'depth' => 0,
            ]);

            if ($organisationUnit->parent_id) {
                // Link ancestors
                DB::statement("
                    INSERT INTO organisation_unit_closure (ancestor_id, descendant_id, depth)
                    SELECT ancestor_id, ?, depth + 1
                    FROM organisation_unit_closure
                    WHERE descendant_id = ?
                ", [$organisationUnit->id, $organisationUnit->parent_id]);
            }
        });
    }
}
