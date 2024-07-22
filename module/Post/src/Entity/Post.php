<?php

namespace Post\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Post\Service\TimeService;

/**
 * @ORM\Entity
 * @ORM\Table(name="posts")
 */
class Post implements InputFilterAwareInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    public $id;

    /**
     * @ORM\Column(name="title", type="string")
     */
    public $title;

    /**
     * @ORM\Column(name="description", type="text")
     */
    public $description;

    /**
     * @ORM\Column(name="image", type="string")
     */
    public $image;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    public $created_at;

    // Add getters and setters for each property
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }
    /**
     * @ORM\ManyToOne(targetEntity= "User\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    public $user;

    private $inputFilter;

    // Getters and Setters...

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

//    public function getCreatedAt()
//    {
//        return $this->created_at;
//    }
//
//    public function setCreatedAt($createdAt)
//    {
//        $this->created_at = $CreatedAt;
//    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getFormattedCreatedAt()
    {
        if ($this->getCreatedAt() != null) {
            $timeService = new TimeService($this->created_at->format('Y-m-d H:i:s'));
            return $timeService->dateToShamsi();
        }
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add([
                'name' => 'title',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 255,
                        ],
                    ],
                ],
            ]);

            $inputFilter->add([
                'name' => 'description',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 255,
                        ],
                    ],
                ],
            ]);

            $inputFilter->add([
                'name' => 'image',
                'required' => false,
            ]);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'title' => $this->getTitle(),
            'created_at' => $this->getCreatedAt(),
            'image' => $this->getImage(),
            'user' => $this->getUser(),
        ];
    }

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->title = !empty($data['title']) ? $data['title'] : null;
        $this->description = !empty($data['description']) ? $data['description'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->image = !empty($data['image']) ? $data['image'] : null;
        $this->user = !empty($data['user']) ? $data['user'] : null;
    }
}
