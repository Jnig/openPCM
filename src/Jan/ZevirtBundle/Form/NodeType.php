<?php

namespace Jan\ZevirtBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Jan\ZevirtBundle\Form\EventListener\ExtraFieldSubscriber;

class NodeType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $subscriber = new ExtraFieldSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);

        $builder
                ->add('name')
                ->add('hostname')
                ->add('password')
                ->add('storages', 'entity', array('by_reference' => false, 'class' => 'JanZevirtBundle:Storage', 'multiple' => true))
                ->add('networks')
                ->add('cluster')
                ->add('corosyncIp1')
                ->add('corosyncIp2')
                ->add('stonith')
                ->add('stonithParameters')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Jan\ZevirtBundle\Entity\Node',
            'csrf_protection' => false
        ));
    }

    public function getName() {
        return '';
    }

}
