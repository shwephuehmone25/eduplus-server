<?php

namespace App\Http\Requests;

use App\Contracts\Likeable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class LikeRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id', 
            'likeable_id' => 'required|integer', 
            'likeable_type' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!class_exists($value) || !in_array(Likeable::class, class_implements($value))) {
                        $fail("$value is not a valid Likeable model.");
                    }
                },
            ],
        ];
    }

   public function likeable(): Likeable
    {
        $likeableType = $this->input('likeable_type');
        $likeableId = $this->input('likeable_id');

        switch ($likeableType) {
            case 'App\Models\Allocation':
                return \App\Models\Allocation::find($likeableId);
                break;
            default:
                break;
        }
    }
}
