<?php

namespace JuanMiguelBesada\DoctrineTranslatableFormBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslatableType extends AbstractType
{
    private $entityManager;

    private $translatableListener;

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
    public function __construct(EntityManagerInterface $entityManager, TranslatableListener $translatableListener, array $locales = [])
    {
        $this->entityManager = $entityManager;
        $this->translatableListener = $translatableListener;
        $this->locales = $locales;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setDataMapper(new DoctrineTranslatableDataMapper($this->entityManager, $this->translatableListener));

        foreach ($options['locales'] as $locale) {
            $typeOptions = $options['type_options'];
            $typeOptions['label'] = Intl::getLocaleBundle()->getLocaleName($locale);

            //Mark the first locale as required if needed
            if ($options['required'] && $options['locales'][0] === $locale) {
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
                'type_options' => [
                    'required' => false,
                ],
                'mapped' => false,
            ])
            ->setNormalizer('type_options', function (Options $options, $typeOptions) {
                if (!isset($typeOptions['required'])) {
                    $typeOptions['required'] = false;
                }

                return $typeOptions;
            });
    }
}
