<?php

namespace JuanMiguelBesada\DoctrineTranslatableFormBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DoctrineTranslatableDataMapper implements DataMapperInterface
{
    private $entityManager;

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
        $empty = null === $data || [] === $data;

        if (!$empty && !\is_array($data) && !\is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

        foreach ($forms as $form) {
            $locale = $form->getName();
            $entity = $this->getTranslatableEntity($form);
            $field = $this->getTranslatableField($form);

            $form->setData($this->getTranslationForField($entity, $locale, $field));
        }
    }

    public function mapFormsToData($forms, &$data)
    {
        if (null === $data) {
            return;
        }

        if (!\is_array($data) && !\is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

        foreach ($forms as $form) {
            $locale = $form->getName();
            $entity = $this->getTranslatableEntity($form);
            $field = $this->getTranslatableField($form);
            $translation = $form->getData();

            $this->translationsRepository->translate($entity, $field, $locale, $translation);
        }
    }

    private function getTranslationForField($entity, $locale, $field)
    {
        $classMetadata = $this->getClassMetadata($entity);
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

    private function getClassMetadata($entity)
    {
        return $this->entityManager->getClassMetadata(get_class($entity));
    }

    private function getTranslatableEntity(FormInterface $form)
    {
        $entity = $form->getParent()->getParent()->getData();

        if ($this->isEmbeddedClass($entity)) {
            $entity = $form->getParent()->getParent()->getParent()->getData();
        }

        return $entity;
    }

    private function getTranslatableField(FormInterface $form)
    {
        $field = $form->getParent()->getName();

        $entity = $form->getParent()->getParent()->getData();
        if ($this->isEmbeddedClass($entity)) {
            $field = $form->getParent()->getParent()->getName() . '.' . $field;
        }

        return $field;
    }

    private function isEmbeddedClass($entity)
    {
        return  $entityMetadata = $this->getClassMetadata($entity)->isEmbeddedClass;
    }
}
