<?php

namespace App\Http\Requests;

use App\Contracts\Likeable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UnlikeRequest extends FormRequest
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
                Rule::in([
                    'App\Models\Course', 
                ]),
            ],
        ];
    }

    public function likeable(): Likeable
    {
        $likeableType = $this->input('likeable_type');
        $likeableId = $this->input('likeable_id');

        switch ($likeableType) {
            case 'App\Models\Course':
                $likeable = \App\Models\Course::find($likeableId);
                break;
            
            default:
                
                throw new \InvalidArgumentException('Unknown likeable_type');
                break;
        }

        if (!$likeable) {
            throw new \InvalidArgumentException('Likeable object not found');
        }

        if (!$likeable instanceof Likeable) {
            throw new \InvalidArgumentException('Likeable object does not implement the Likeable interface');
        }

        return $likeable;
    }
}
