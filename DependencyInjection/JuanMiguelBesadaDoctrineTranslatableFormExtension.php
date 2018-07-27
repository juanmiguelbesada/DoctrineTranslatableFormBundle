<?php

namespace JuanMiguelBesada\DoctrineTranslatableFormBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class JuanMiguelBesadaDoctrineTranslatableFormExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $definition = $container->getDefinition('JuanMiguelBesada\DoctrineTranslatableFormBundle\Form\TranslatableType');
        $definition->setArgument(1, $config['locales']);

        $this->registerFormTheme($container);
    }

    private function registerFormTheme(ContainerBuilder $container)
    {
        $resources = $container->hasParameter('twig.form.resources') ? $container->getParameter('twig.form.resources') : [];
        $resources[] = '@JuanMiguelBesadaDoctrineTranslatableForm/form_theme.html.twig';
        $container->setParameter('twig.form.resources', $resources);
    }
}
