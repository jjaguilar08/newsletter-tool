<?php

declare(strict_types=1);

namespace App\Http\Requests\Subscribers;

use App\Enums\SubscriberStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Emails are case-insensitive in practice; normalizing here means the
    // uniqueness rule below (and the value actually stored) never depends on
    // the DB connection's collation - see PROJECT_NOTES.md Day 9.
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => Str::lower((string) $this->input('email'))]);
        }
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
