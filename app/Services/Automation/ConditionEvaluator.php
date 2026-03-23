<?php

namespace App\Services\Automation;

use App\Models\Ticket;

/**
 * Evaluates a JSON condition tree against a ticket context.
 *
 * Condition tree schema:
 * {
 *   "operator": "AND" | "OR",          // top-level logical operator
 *   "conditions": [
 *     { "field": "priority", "op": "equals", "value": "high" },
 *     { "field": "status",   "op": "not_equals", "value": "closed" },
 *     { "field": "subject",  "op": "contains", "value": "urgent" },
 *     ...
 *   ]
 * }
 *
 * Supported field operators: equals, not_equals, contains, not_contains,
 *                             starts_with, ends_with, greater_than, less_than,
 *                             is_null, is_not_null
 */
class ConditionEvaluator
{
    /**
     * Evaluate the condition tree against the given ticket.
     *
     * @param  array<string, mixed>  $tree  Decoded JSON condition tree
     */
    public function evaluate(array $tree, Ticket $ticket): bool
    {
        $operator   = strtoupper($tree['operator'] ?? 'AND');
        $conditions = $tree['conditions'] ?? [];

        if (empty($conditions)) {
            return true; // no conditions → always matches
        }

        $results = array_map(
            fn (array $condition) => $this->evaluateCondition($condition, $ticket),
            $conditions
        );

        return $operator === 'OR'
            ? in_array(true, $results, true)
            : ! in_array(false, $results, true);
    }

    /**
     * Evaluate a single condition node.
     *
     * @param  array{field: string, op: string, value?: mixed}  $condition
     */
    private function evaluateCondition(array $condition, Ticket $ticket): bool
    {
        $field    = $condition['field'] ?? '';
        $op       = $condition['op'] ?? 'equals';
        $expected = $condition['value'] ?? null;

        // Support dot-notation for custom_fields (e.g. "custom_fields.department")
        $actual = $this->resolveField($ticket, $field);

        return match ($op) {
            'equals'       => $actual == $expected,
            'not_equals'   => $actual != $expected,
            'contains'     => str_contains((string) $actual, (string) $expected),
            'not_contains' => ! str_contains((string) $actual, (string) $expected),
            'starts_with'  => str_starts_with((string) $actual, (string) $expected),
            'ends_with'    => str_ends_with((string) $actual, (string) $expected),
            'greater_than' => is_numeric($actual) && is_numeric($expected) && $actual > $expected,
            'less_than'    => is_numeric($actual) && is_numeric($expected) && $actual < $expected,
            'is_null'      => $actual === null,
            'is_not_null'  => $actual !== null,
            default        => false,
        };
    }

    /**
     * Resolve a field path from the ticket, supporting dot-notation for custom_fields.
     */
    private function resolveField(Ticket $ticket, string $field): mixed
    {
        if (str_starts_with($field, 'custom_fields.')) {
            $key = substr($field, strlen('custom_fields.'));

            return data_get($ticket->custom_fields, $key);
        }

        return $ticket->{$field} ?? null;
    }
}
