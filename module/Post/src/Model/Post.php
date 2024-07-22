<?php

namespace Post\Model;

use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Post\Service\TimeService;

class Post implements InputFilterAwareInterface
{
    public $id;
    public $title;
    public $description;
    public $user;
    public $created_at;
    private $inputFilter;
    public $image;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->title = !empty($data['title']) ? $data['title'] : null;
        $this->description = !empty($data['description']) ? $data['description'] : null;
        if (!empty($data['created_at'])) {
            $timeService = new TimeService($data['created_at']);
            $this->created_at = $timeService->dateToShamsi();
        }
//        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->user = !empty($data['user']) ? $data['user'] : null;
        $this->image = !empty($data['image']) ? $data['image'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'title' => $this->getTitle(),
            'created_at' => $this->getCreatedAt(),
            'user' => $this->getUser(),
            'image' => $this->getImage(),
        ];
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf(
            '%s does not allow injection of an alternate input filter',
            __CLASS__
        ));
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'id',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'description',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'title',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }
}
