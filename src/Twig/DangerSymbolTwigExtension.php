<?php

namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DangerSymbolTwigExtension extends AbstractExtension
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getDangerSymbolBase64', [$this, 'getDangerSymbolBase64']),
        ];
    }

    public function getDangerSymbolBase64($iconFilename)
    {
        $path = $this->params->get('kernel.project_dir') . '/public/uploads/icon_symboles_de_danger/' . $iconFilename;

        if (file_exists($path)) {
            $data = file_get_contents($path);
            return base64_encode($data);
        }

        return '';
    }
}