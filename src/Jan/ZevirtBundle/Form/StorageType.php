<?php

namespace Jan\ZevirtBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StorageType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('id')
                ->add('name')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Jan\ZevirtBundle\Entity\Storage',
            'csrf_protection' => false
        ));
    }

    public function getName() {
        return '';
    }

}
