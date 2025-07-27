<?php

namespace App\View\Components;

use App\Contacts\MediaInterface;
use Illuminate\View\Component;

class MediaImage extends Component
{
    public $model;
    public $collection;
    public $conversion;
    public $url;
    public $alt;

    public function __construct(
        MediaInterface $model,
        string $collection = 'default',
        string $conversion = '',
        string $alt = ''
    ) {
        $this->model = $model;
        $this->collection = $collection;
        $this->conversion = $conversion;
        $this->alt = $alt;
        $this->url = $model->getMediaUrl($collection, $conversion);
    }

    public function render()
    {
        return view('components.media-image');
    }
}
