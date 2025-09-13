<?php

namespace App\Traits\Services;

use Illuminate\Support\Facades\DB;

/**
 * Handle dynamic method calls for store and update operations, wrapping them in a DB transaction.
 *
 * This magic method is triggered when a non-existent or inaccessible method is called.
 * It is primarily used to wrap `store` and `update` methods in database transactions automatically
 * i put it protected to be unvisible when calling it from controller -> in this time will go into __call() this method contain DB::transaction to apply transaction on these methods
 *
 * Example usage:
 * $service->store($requestArray, $model);         // Will call store($request, $model)
 * $service->update($requestArray, $id, $model);   // Will call update($request, $id, $model )
 *
 * @param string $method The name of the method being called (e.g. "store" or "update").
 * @param array $args The arguments passed to the method. These are unpacked and passed to the actual method.
 *                    - For `store`: $args = [$request, $model]
 *                    - For `update`: $args = [$request, $id, $model]
 *
 * @return mixed The result of the called method inside the transaction.
 *
 * @throws \BadMethodCallException If the method is not defined in the list of transaction-wrapped methods.
 */
trait HandlesServiceTransactions
{
    /**
     * The service methods that should be executed within a DB transaction.
     */
    protected array $transactionalMethods = [
        'store',
        'update',
        'destroy',
        'restore',
    ];

    /**
     * Intercept calls to protected methods and run them inside a transaction if needed.
     */
    public function __call($method, $arguments)
    {
        if (in_array($method, $this->transactionalMethods)) {
            return DB::transaction(fn() => call_user_func_array([$this, $method], $arguments));
        }

        return call_user_func_array([$this, $method], $arguments);
    }

}
