<?php
namespace App\Repositories\User\Payment;

use App\Repositories\User\Payment\PaymentMethodRepositoryInterface;
use App\Repositories\Eloquent\EloquentRepository;
use Illuminate\Support\Facades\Session;

/**
 * PaymentRepository
 *
 * This is a PaymentMethod Repository class implementing the PaymentRepositoryInterface.
 * It provides methods such as : getData, show
 */
class PaymentMethodRepository extends EloquentRepository implements PaymentMethodRepositoryInterface
{
    // Add specific  Handling data methods here

     #region Constructor
     
    #endregion Constructor

    #region ===================== Start CRUD Methods extends from EloquentRepository: getData($model), show($model, $id) =====================

    #region ===================== End CRUD Methods =====================

}
