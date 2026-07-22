<?php

declare(strict_types=1);

namespace App\Http\Requests\Subscribers;

use App\Enums\SubscriberStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('subscribers', 'email')],
            'name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(SubscriberStatus::class)],
        ];
    }
}
