<?php

namespace App\Form;

use App\Entity\Podcast;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PodcastType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo')
            ->add('descripcion')
            ->add('audio', FileType::class, [
                'label' => 'Archivo Podcast',
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                        'mimeTypes' => [
                            'audio/mpeg', // mp3
                            'audio/ogg', // ogg
                            'audio/mp4', // m4a
                        ],
                        'mimeTypesMessage' => 'Por favor, sube un archivo de audio válido (mp3, ogg, m4a).',
                    ])
                ],
                'data_class' => null
            ])
            ->add('imagen', FileType::class, [
                'label' => 'Imagen',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg', // jpg
                            'image/png', // png
                            'image/webp', // webp
                        ],
                        'mimeTypesMessage' => 'Por favor, sube un archivo de imagen válido (jpg, png, webp).',
                    ])
                ],
                'data_class' => null
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Podcast::class,
        ]);
    }
}
