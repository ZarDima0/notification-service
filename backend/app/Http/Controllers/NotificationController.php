<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Notification\UseCase\CreateBulkNotificationUseCase;
use App\Domain\Notification\UseCase\GetRecipientNotificationsUseCase;
use App\Http\Requests\BulkNotificationRequest;
use App\Http\Requests\RecipientHistoryRequest;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    /**
     * @param BulkNotificationRequest $request
     * @param CreateBulkNotificationUseCase $useCase
     * @return JsonResponse
     */
    public function bulk(BulkNotificationRequest $request, CreateBulkNotificationUseCase $useCase): JsonResponse
    {
        $batchId = $useCase->execute($request->getCreateBulkNotificationDTO());
        return response()->json([
            'batch_id' => $batchId,
            'status' => 'queued',
        ]);
    }

    /**
     * @param int $recipientId
     * @param RecipientHistoryRequest $request
     * @param GetRecipientNotificationsUseCase $useCase
     * @return JsonResponse
     */
    public function recipientHistory(
        int $recipientId,
        RecipientHistoryRequest $request,
        GetRecipientNotificationsUseCase $useCase
    ): JsonResponse {
        $paginator = $useCase->execute($recipientId, $request->getPerPage());
        return response()->json([
            'data' => NotificationResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }
}
