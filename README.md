# DoctrineTranslatableFormBundle

This bundle add a new FormType to simplify the creation of translatable forms using Gedmo Doctrine Extensions and StofDoctrineExtensionsBundle.

Installation
============

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require juanmiguelbesada/doctrine-translatable-form-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require juanmiguelbesada/doctrine-translatable-form-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new JuanMiguelBesada\DoctrineTranslatableFormBundle\JuanMiguelBesadaDoctrineTranslatableFormBundle(),
        );

        // ...
    }

    // ...
}
```

### Step 3: Configure the Bundle

Lastly, configure the default languages used by the TranslatableType

```yaml
juan_miguel_besada_doctrine_translatable_form:
    locales: ['es', 'en', 'fr'] #you can add as much as you need
```

Usage
============

```php
<?php

namespace AppBundle\Form;

use JuanMiguelBesada\DoctrineTranslatableFormBundle\Form\TranslatableType;

class CategoryType extends AbstractTranslatableType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // you can add the translatable fields
        $this
             ->add("name", TranslatableType::class, array(
                 'type' => TextType::class,
                 'type_options' => array(
                     'required' => false,
                     ...
                 )
             ))
             ->add("description", TranslatableType::class, array(
                 'type' => TextareaType::class,
                 'locales' => array('es', 'fr', 'de', 'gl'), //Define custom languages
                 'type_options' => array(
                     'attr' => array(
                         'class' => 'my_class'
                     ),
                     ...
                 )
             ))
        ;

        // and then you can add the rest of the fields using the standard way
        $builder->add('enabled')
        ;

    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(
            'data_class'   => 'AppBundle\Entity\Category'
        ));
    }
}
```