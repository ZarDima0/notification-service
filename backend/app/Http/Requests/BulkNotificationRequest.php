<?php

namespace App\Http\Requests;

use App\Domain\Notification\DTO\CreateBulkNotificationDTO;
use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Enums\Priority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class BulkNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'channel' => [
                'required',
                new Enum(NotificationChannel::class),
            ],
            'message' => [
                'required',
                'string',
                'max:1000',
            ],
            'priority' => [
                'required',
                'in:high,low,marketing',
            ],
            'recipients' => [
                'required',
                'array',
                'min:1',
            ],
            'recipients.*' => [
                'integer',
            ],
            'idempotency_key' => [
                'required',
                'string',
            ],
        ];
    }

    public function getCreateBulkNotificationDTO(): CreateBulkNotificationDTO
    {
        return new CreateBulkNotificationDTO(
            NotificationChannel::from($this->input('channel')),
            $this->input('message'),
            Priority::from($this->input('priority')),
            $this->input('recipients'),
            $this->input('idempotency_key'),
        );
    }
}
