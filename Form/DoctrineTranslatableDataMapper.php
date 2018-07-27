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

    /**
     * @var array
     */
    private $translations = [];

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

            $form->setData($this->getTranslationForField($entity, $locale, $field));
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

    private function getTranslationForField($entity, $locale, $field)
    {
        $classMetadata = $this->entityManager->getClassMetadata(get_class($entity));
        $currentLocale = $this->translatableListener->getTranslatableLocale($entity, $classMetadata, $this->entityManager);

        if ($locale === $currentLocale) {
            $accessor = PropertyAccess::createPropertyAccessor();

            return $accessor->getValue($entity, $field);
        }

        //we need the translations
        $translations = $this->getEntityTranslations($entity);
        if (isset($translations[$locale][$field])) {
            return $translations[$locale][$field];
        }

        return '';
    }

    private function getEntityTranslations($entity)
    {
        if (!count($this->translations)) {
            $this->translations = $this->translationsRepository->findTranslations($entity);
        }

        return $this->translations;
    }
}
