<?php

namespace ProtoneMedia\LaravelFFMpeg\Drivers;

use Exception;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Support\Arr;
use ProtoneMedia\LaravelFFMpeg\Filesystem\Media;
use ProtoneMedia\LaravelFFMpeg\Filesystem\MediaCollection;

trait InteractsWithMediaStreams
{
    /**
     * Returns an array with all streams.
     *
     * @return array
     */
    public function getStreams(): array
    {
        if (!$this->isAdvancedMedia()) {
            return iterator_to_array($this->media->getStreams());
        }

        return $this->mediaCollection->map(function ($media) {
            return $this->fresh()->open(MediaCollection::make([$media]))->getStreams();
        })->collapse()->all();
    }

    /**
     * Gets the duration of the media from the first stream or from the format.
     */
    public function getDurationInMiliseconds(): int
    {
        $stream = Arr::first($this->getStreams());

        if ($stream->has('duration')) {
            return $stream->get('duration') * 1000;
        }

        $format = $this->getFormat();

        if ($format->has('duration')) {
            return $format->get('duration') * 1000;
        }

        throw new Exception('Opened media is not a video file.');
    }

    public function getDurationInSeconds(): int
    {
        return round($this->getDurationInMiliseconds() / 1000);
    }

    /**
     * Gets the first audio streams of the media.
     */
    public function getAudioStream(): ?Stream
    {
        return Arr::first($this->getStreams(), function (Stream $stream) {
            return $stream->isAudio();
        });
    }

    /**
     * Gets the first video streams of the media.
     */
    public function getVideoStream(): ?Stream
    {
        return Arr::first($this->getStreams(), function (Stream $stream) {
            return $stream->isVideo();
        });
    }
}
