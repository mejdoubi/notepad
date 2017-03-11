<?php

namespace NoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use NoteBundle\Entity\Category;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Note
 *
 * @ORM\Table(name="note")
 * @ORM\Entity(repositoryClass="NoteBundle\Repository\NoteRepository")
 */
class Note
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * Many notes have One category.
     * @ORM\ManyToOne(targetEntity="NoteBundle\Entity\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->setDate(new \DateTime('today'));
    }

    /**
     * @Assert\isTrue (message = "XML content is invalid")
     */
    public function isValid()
    {
        $dom = new \DomDocument();
        $header = "<?xml version='1.0' encoding='UTF-8'?>";
        $content = $this->getContent();
        $xml = $header."<content>".$content."</content>";
        try {
            $dom->loadXML($xml);
            if(!@$dom->schemaValidate("note.xsd")) {
                return false;
            }
            else {
                return true;
            }
        } catch (\ErrorException $e) {
            return false;
        }
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Note
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Note
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Note
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set category
     *
     * @param $category
     *
     * @return Category
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return category
     */
    public function getCategory()
    {
        return $this->category;
    }
}

?>