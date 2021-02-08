<?php
/**
 * A reduced version of LeKoala\DebugBar\Extension\ProxyDBExtension
 */

namespace Gurucomkz\EagerLoading;

use SilverStripe\Core\Extension;
use SilverStripe\ORM\DB;
use TractorCow\ClassProxy\Generators\ProxyGenerator;

class ProxyDBCounterExtension extends Extension
{

    /**
     * Store queries
     *
     * @var array
     */
    protected static $queries = [];


    public function updateProxy(ProxyGenerator &$proxy)
    {
        // In the closure, $this is the proxied database
        $callback = function ($args, $next) {

            // The first argument is always the sql query
            $sql = $args[0];
            $parameters = isset($args[2]) ? $args[2] : [];

            // Sql can be an array
            // TODO: verify if it's still the case in SS4
            if (is_array($sql)) {
                $parameters = $sql[1];
                $sql = $sql[0];
            }

            // Inline sql
            $sql = DB::inline_parameters($sql, $parameters);

            // Execute all middleware
            $handle = $next(...$args);

            // Sometimes, ugly spaces are there
            $sql = preg_replace('/[[:blank:]]+/', ' ', trim($sql));

            self::$queries[] = [
                'query' => $sql,
                'rows' => $handle ? $handle->numRecords() : null,
            ];
            // echo "\nQuery: $sql\n";

            return $handle;
        };

        // Attach to benchmarkQuery to fire on both query and preparedQuery
        $proxy = $proxy->addMethod('benchmarkQuery', $callback);
    }

    public static function resetQueries()
    {
        self::$queries = [];
    }

    public static function getQueries()
    {
        return self::$queries;
    }

    public static function getQueriesCount()
    {
        return count(self::$queries);
    }
}
