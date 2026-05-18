<?php

namespace App\Http\Requests;

use App\Domain\Notification\DTO\CreateBulkNotificationDTO;
use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Enums\Priority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RecipientHistoryRequest extends FormRequest
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
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    public function getPerPage(): int
    {
        return min(100, max(1, (int) $this->input('per_page', 20)));
    }
}
