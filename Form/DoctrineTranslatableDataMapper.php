<?php

namespace JuanMiguelBesada\DoctrineTranslatableFormBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DoctrineTranslatableDataMapper implements DataMapperInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TranslatableListener
     */
    private $translatableListener;

    /**
     * @var TranslationRepository
     */
    private $translationsRepository;

    public function __construct(EntityManagerInterface $entityManager, TranslatableListener $translatableListener)
    {
        $this->entityManager = $entityManager;
        $this->translatableListener = $translatableListener;
        $this->translationsRepository = $this->entityManager->getRepository(Translation::class);
    }

    public function mapDataToForms($data, $forms)
    {
        foreach ($forms as $form) {
            //$entity = $form->getRoot()->getData();
            $entity = $form->getParent()->getParent()->getData();
            $field = $form->getParent()->getName();
            $locale = $form->getName();

            $this->loadEntityInLocale($entity, $locale);

            $accessor = PropertyAccess::createPropertyAccessor();
            $form->setData($accessor->getValue($entity, $field));
        }
    }

    public function mapFormsToData($forms, &$data)
    {
        foreach ($forms as $form) {
            //$entity = $form->getRoot()->getData();
            $entity = $form->getParent()->getParent()->getData();
            $field = $form->getParent()->getName();
            $locale = $form->getName();
            $translation = $form->getData();

            $this->translationsRepository->translate($entity, $field, $locale, $translation);
        }
    }

    private function loadEntityInLocale($entity, $locale)
    {
        $classMetadata = $this->entityManager->getClassMetadata(get_class($entity));
        $currentLocale = $this->translatableListener->getTranslatableLocale($entity, $classMetadata, $this->entityManager);

        if ($locale === $currentLocale) {
            return;
        }

        $configuration = $this->translatableListener->getConfiguration($this->entityManager, $classMetadata->name);
        $property = $classMetadata->getReflectionClass()->getProperty($configuration['locale']);
        $property->setAccessible(true);
        $property->setValue($entity, $locale);
        $this->entityManager->refresh($entity);
    }
}
