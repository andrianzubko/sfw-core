<?php

namespace SFW\Lazy;

/**
 * Device and OS detector.
 */
class Detector extends \App\Lazy
{
    /**
     * Device: 'mobile' or 'desktop'
     */
    public string $device;

    /**
     * OS: 'android', 'winphone', 'ios' or 'other'. At desktop alltimes 'other'.
     */
    public string $os;

    /**
     * Very simple and very fast detector. Enough for CSS.
     */
    public function __construct()
    {
        if (preg_match('/\b(iphone|ipad|ipod|ios|android|mobile|phone)\b/', strtolower($_SERVER['HTTP_USER_AGENT'] ?? ''), $M)) {
            $this->device = 'mobile';

            if ($M[1] === 'android') {
                $this->os = 'android';
            } elseif ($M[1] === 'mobile' || $M[1] === 'phone') {
                $this->os = 'winphone';
            } else {
                $this->os = 'ios';
            }
        } else {
            $this->device = 'desktop';

            $this->os = 'other';
        }
    }
}
