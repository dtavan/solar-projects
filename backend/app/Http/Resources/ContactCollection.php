<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ContactCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($item) {
            return [
                'type' => 'contacts',
                'id' => $item->uuid,
                'links' => [
                    'self' => [
                        'href' => ContactResource::getSelfLink($item),
                    ],
                ],
            ];
        });
    }
}
