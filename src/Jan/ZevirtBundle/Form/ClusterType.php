<?php

namespace Jan\ZevirtBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClusterType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('id')
                ->add('name')
                ->add('ha')
                ->add('storages', null, array('by_reference' => false))
                ->add('networks')

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Jan\ZevirtBundle\Entity\Cluster',
            'csrf_protection' => false
        ));
    }

    public function getName() {
        return '';
    }

}
