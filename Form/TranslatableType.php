<?php

namespace JuanMiguelBesada\DoctrineTranslatableForm\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslatableType extends AbstractType
{
    /**
     * @var DoctrineTranslatableDataMapper
     */
    private $mapper;

    /**
     * @var array
     */
    private $locales;

    /**
     * TranslatableType constructor.
     *
     * @param DoctrineTranslatableDataMapper $mapper
     * @param array                          $locales
     */
    public function __construct(DoctrineTranslatableDataMapper $mapper, array $locales = [])
    {
        $this->mapper = $mapper;
        $this->locales = $locales;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setDataMapper($this->mapper);

        foreach ($options['locales'] as $locale) {
            $typeOptions = $options['type_options'];
            $typeOptions['label'] = Intl::getLocaleBundle()->getLocaleName($locale);

            //Mark the first locale as required if needed
            if ($options['required'] && $locale === $options['locales'][0]) {
                $typeOptions['required'] = true;
            }

            $builder->add($locale, $options['type'], $typeOptions);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('type')
            ->setDefaults([
                'locales' => $this->locales,
                'type_options' => [],
                'mapped' => false,
            ]);
    }
}