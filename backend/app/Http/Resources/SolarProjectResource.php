<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SolarProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'type' => 'solar_projects',
            'id' => $this->uuid,
            'attributes' => [
                'title' => $this->title,
                'system_size' => $this->system_size,
                'system_details' => $this->system_details,
                'site_latitude' => $this->site_latitude,
                'site_longitude' => $this->site_longitude,
                'created_at' => $this->created_at->toAtomString(),
                'updated_at' => $this->updated_at->toAtomString(),
            ],
            'links' => [
                'self' => [
                    'href' => self::getSelfLink($this),
                ],
                'contacts' => [
                    'href' => self::getContactsLink($this),
                ],
            ],
        ];
    }

    public static function getSelfLink($item)
    {
        return action('SolarProjectsController@show', ['solar_project' => $item->uuid]);
    }

    public static function getContactsLink($item)
    {
        return action('SolarProjectContactsController@index', ['solar_project' => $item->uuid]);
    }
}
