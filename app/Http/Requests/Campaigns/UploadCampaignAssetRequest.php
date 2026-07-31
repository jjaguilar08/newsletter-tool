<?php

declare(strict_types=1);

namespace App\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;

class UploadCampaignAssetRequest extends FormRequest
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
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ];
    }
}
