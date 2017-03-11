<?php

namespace NoteBundle\Controller;

use NoteBundle\Entity\Note;
use NoteBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ApiController extends Controller
{   
    /**
     * @Route("/api/notes", name="APInotes")
     * @Method("GET")
     */
    public function listNotesAction()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $em = $this->getDoctrine()->getManager();

        $notes = $em->getRepository('NoteBundle:Note')->findAll();
        if (!$notes) {
            $response = array('error' => "notes not found");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
        $jsonContent = $serializer->serialize($notes, 'json');
        return new Response($jsonContent);
    }

    /**
     * @Route("/api/note/g", name="APIgetNote")
     * @Method("GET")
     */
    public function getNoteAction(Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $em = $this->getDoctrine()->getManager();

        $id = $request->query->get('id');
        if(!$id) {
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
        $note = $em->getRepository('NoteBundle:Note')->find($id);
        if (!$note) {
            $response = array('error' => "note not found");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
        $jsonContent = $serializer->serialize($note, 'json');
        return new Response($jsonContent);
    }

    /**
     * @Route("/api/categories", name="APIcategories")
     * @Method("GET")
     */
    public function listCategoriesAction()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('NoteBundle:Category')->findAll();
        if (!$categories) {
            $response = array('error' => "categories not found");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
        $jsonContent = $serializer->serialize($categories, 'json');
        return new Response($jsonContent);
    }

    /**
     * @Route("/api/note/n", name="APInewNote")
     * @Method("POST")
     */
    public function newNoteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);
        
        try {
            $title = $data['title'];
            $content = $data['content'];
            $date = new \DateTime($data['date']);
            $categoryId = $data['categoryId'];
        } catch (\ErrorException $e) {
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
        
        $note = new Note();
        $note->setTitle($title);
        $note->setContent($content);
        $note->setDate($date);
        $category = $em->getRepository('NoteBundle:Category')->find($categoryId);
        if (!$category) {
            $response = array('error' => "category not found");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
        $note->setCategory($category);

        try {
            $em->persist($note);
            $em->flush();
            $response = array('success' => true);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        } catch(Exception $e) {
            $response = array('success' => false);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
    }

    /**
     * @Route("/api/note/e", name="APIeditNote")
     * @Method("PUT")
     */
    public function editNoteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);
        
        try {
            $id = $data['id'];
            $title = $data['title'];
            $content = $data['content'];
            $date = new \DateTime($data['date']);
            $categoryId = $data['categoryId'];
        } catch (\ErrorException $e) {
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }

        $note = $em->getRepository('NoteBundle:Note')->find($id);
        if (!$note) {
            $response = array('error' => "note not found");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
        $note->setTitle($title);
        $note->setContent($content);
        $note->setDate($date);
        $category = $em->getRepository('NoteBundle:Category')->find($categoryId);
        if (!$category) {
            $response = array('error' => "category not found");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
        $note->setCategory($category);

        try {
            $em->flush();
            $response = array('success' => true);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        } catch(Exception $e) {
            $response = array('success' => false);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
    }

    /**
     * @Route("/api/note/d", name="APIdelNote")
     * @Method("DELETE")
     */
    public function delNoteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);

        try {
            $id = $data['id'];
        } catch (\ErrorException $e) {
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }

        $note = $em->getRepository('NoteBundle:Note')->find($id);
        if (!$note) {
            $response = array('error' => "note not found");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }

        try {
            $em->remove($note);
            $em->flush();
            $response = array('success' => true);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        } catch(Exception $e) {
            $response = array('success' => false);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
    }

    /**
     * @Route("/api/category/n", name="APInewCategory")
     * @Method("POST")
     */
    public function newCategoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);
        
        try {
            $label = $data['label'];
        } catch (\ErrorException $e) {
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
        $category = new Category();
        $category->setLabel($label);

        try {
            $em->persist($category);
            $em->flush();
            $response = array('success' => true);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        } catch(Exception $e) {
            $response = array('success' => false);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
    }

    /**
     * @Route("/api/category/e", name="APIeditCategory")
     * @Method("PUT")
     */
    public function editCategoryAction(Request $request)
    {   
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);
        
        try {
            $id = $data['id'];
            $label = $data['label'];
        } catch (\ErrorException $e) {
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }

        $category = $em->getRepository('NoteBundle:Category')->find($id);
        if (!$category) {
            $response = array('error' => "category not found");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
        $category->setLabel($label);

        try {
            $em->flush();
            $response = array('success' => true);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        } catch(Exception $e) {
            $response = array('success' => false);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
    }

    /**
     * @Route("/api/category/d", name="APIdelCategory")
     * @Method("DELETE")
     */
    public function delCategoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);
        
        try {
            $id = $data['id'];
        } catch (\ErrorException $e) {
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }

        $category = $em->getRepository('NoteBundle:Category')->find($id);
        if (!$category) {
            $response = array('error' => "category not found");
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }

        try {
            $em->remove($category);
            $em->flush();
            $response = array('success' => true);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        } catch(Exception $e) {
            $response = array('success' => false);
            $jsonContent = json_encode($response);
            return new Response($jsonContent);
        }
    }
}

?>