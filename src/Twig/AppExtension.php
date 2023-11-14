<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('img_path', [$this, 'imagePath']),
        ];
    }

    public function imagePath(string $path): string
    {
        if (str_contains($path, '://')) {
            return $path;
        } else {
            return '/uploads/'.$path;
        }
    }
}
