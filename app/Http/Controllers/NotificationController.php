<?php

namespace App\Http\Controllers;

use App\Services\General\SendingNotificationMethods\SendingNotificationsService;
use App\Repositories\User\NotificationService;
use App\Models\Notification;
use App\Resources\NotificationResource;
use App\Http\Requests\UpdateFcmRequest;
use App\Http\Requests\SendNotificationRequest;

/**
 * Class NotificationController
 *
 * This controller handles user notifications including:
 * - Listing notifications for web and API.
 * - Updating Firebase Cloud Messaging (FCM) token.
 * - Sending a custom notification to a user.
 */
class NotificationController extends Controller
{
    #region Constructor

    /**
     * Notification repository instance.
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Notification model instance.
     *
     * @var Notification
     */
    protected $notification;

    /**
     * NotificationController constructor.
     *
     * @param Notification $notification
     * @param NotificationService $notificationService
     */
    public function __construct(Notification $notification, NotificationService $notificationService)
    {
        $this->notification = $notification;
        $this->notificationService = $notificationService;
    }
    #endregion Constructor
    

    #region ===================== Start CRUD Methods: store($request, $model), update($request, $id, $model), forceDelete($id, $model), forceDeleteMany($request, $model) =====================
    
    /**
     * Display a list of notifications.
     *
     * Handles both Web and API responses.
     * - Web: returns an Inertia view with breadcrumbs and form data.
     * - API: returns a JSON response with resource formatting.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index()
    {
        // Get data using the notification service
        $result = $this->notificationService->getData($this->notification);

        // Handle web request
        if ($this->isWebRequest()) {
            // Set breadcrumb for UI
            $this->breadcrumb([
                ['label' => __('notifications'), 'url' => route('notifications.index')],
            ]);

            // Render Inertia view for web
            return $this->renderWebIndexPage('Notification/Index', [
                'rows' => $result,
                'form_data' => $this->getCreateUpdateData(),
            ]);
        }

        // Handle API response with resource formatting
        return $this->respond($result, NotificationResource::class);
    }

    #region ===================== End CRUD Methods  =====================

    /**
     * Update the FCM token for the authenticated user.
     *
     * @param UpdateFcmRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFcm(UpdateFcmRequest $request)
    {
        // Validate and update FCM token
        $data = $request->validated();
        $user = userApi()->update(['fcm_token' => $data['fcm_token']]);

        // Return updated user data
        return $this->respond($user, NotificationResource::class);
    }

    /**
     * Send a notification to a specific user.
     *
     * @param SendNotificationRequest $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendNotificationMethod(SendNotificationRequest $request, $userId)
    {
        // Validate input and send notification via the service
        $data = $request->validated();
        $notification = app(SendingNotificationsService::class)
            ->sendNotification($data, $userId, $type = null);

        // Return notification with eager loaded relations
        return $this->respond($notification->load($this->notification->getProp('eagerLoading')));
    }
}
