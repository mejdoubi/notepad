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
     * @Route("/api/notes", name="APInotesGetAll")
     * @Method({"OPTIONS", "GET"})
     */
    public function listNotesAction(Request $request)
    {
        // CORS preflight request
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/text');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
            return $response;
        }

        $resp = new Response();
        $resp->headers->set('Content-Type', 'application/json');
        $resp->headers->set('Access-Control-Allow-Origin', '*');
        $resp->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $em = $this->getDoctrine()->getManager();

        $notes = $em->getRepository('NoteBundle:Note')->findAll();
        if (!$notes) {
            $resp->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error' => "notes not found");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
        $resp->setStatusCode(Response::HTTP_OK);
        $jsonContent = $serializer->serialize($notes, 'json');
        $resp->setContent($jsonContent);
        return $resp;
    }

    /**
     * @Route("/api/notes/{id}", name="APInotesGetOne", requirements={"id": "\d+"})
     * @Method({"OPTIONS", "GET"})
     */
    public function getNoteAction(Request $request, $id)
    {
        // CORS preflight request
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/text');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
            return $response;
        }

        $resp = new Response();
        $resp->headers->set('Content-Type', 'application/json');
        $resp->headers->set('Access-Control-Allow-Origin', '*');
        $resp->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $em = $this->getDoctrine()->getManager();

        if(!$id) {
            $resp->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
        $note = $em->getRepository('NoteBundle:Note')->find($id);
        if (!$note) {
            $resp->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error' => "note not found");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
        $resp->setStatusCode(Response::HTTP_OK);
        $jsonContent = $serializer->serialize($note, 'json');
        $resp->setContent($jsonContent);
        return $resp;
    }

    /**
     * @Route("/api/categories", name="APIcategoriesGetAll")
     * @Method({"OPTIONS", "GET"})
     */
    public function listCategoriesAction(Request $request)
    {
        // CORS preflight request
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/text');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
            return $response;
        }

        $resp = new Response();
        $resp->headers->set('Content-Type', 'application/json');
        $resp->headers->set('Access-Control-Allow-Origin', '*');
        $resp->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('NoteBundle:Category')->findAll();
        if (!$categories) {
            $resp->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error' => "categories not found");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
        $resp->setStatusCode(Response::HTTP_OK);
        $jsonContent = $serializer->serialize($categories, 'json');
        $resp->setContent($jsonContent);
        return $resp;
    }

    /**
     * @Route("/api/notes", name="APInotesCreate")
     * @Method({"OPTIONS", "POST"})
     */
    public function newNoteAction(Request $request)
    {
        // CORS preflight request
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/text');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
            return $response;
        }

        $resp = new Response();
        $resp->headers->set('Content-Type', 'application/json');
        $resp->headers->set('Access-Control-Allow-Origin', '*');
        $resp->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);
        
        try {
            $title = $data['title'];
            $content = $data['content'];
            $categoryId = $data['categoryId'];
        } catch (\ErrorException $e) {
            $resp->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
        
        $note = new Note();
        $note->setTitle($title);
        $note->setContent($content);
        $note->setDate(new \DateTime('today'));
        $category = $em->getRepository('NoteBundle:Category')->find($categoryId);
        if (!$category) {
            $resp->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error' => "category not found");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
        $note->setCategory($category);

        try {
            $em->persist($note);
            $em->flush();
            $resp->setStatusCode(Response::HTTP_OK);
            $uri = 'api/notes/'.$note->getId();
            $response = array('success' => true, 'uri' => $uri);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        } catch(Exception $e) {
            $resp->setStatusCode(Response::HTTP_FORBIDDEN);
            $response = array('failure' => true);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
    }

    /**
     * @Route("/api/notes", name="APInotesUpdate")
     * @Method({"OPTIONS", "PUT"})
     */
    public function editNoteAction(Request $request)
    {
        // CORS preflight request
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/text');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
            return $response;
        }

        $resp = new Response();
        $resp->headers->set('Content-Type', 'application/json');
        $resp->headers->set('Access-Control-Allow-Origin', '*');
        $resp->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);
        
        try {
            $id = $data['id'];
            $title = $data['title'];
            $content = $data['content'];
            $categoryId = $data['categoryId'];
        } catch (\ErrorException $e) {
            $resp->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }

        $note = $em->getRepository('NoteBundle:Note')->find($id);
        if (!$note) {
            $resp->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error' => "note not found");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
        $note->setTitle($title);
        $note->setContent($content);
        $category = $em->getRepository('NoteBundle:Category')->find($categoryId);
        if (!$category) {
            $resp->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error' => "category not found");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
        $note->setCategory($category);

        try {
            $em->flush();
            $resp->setStatusCode(Response::HTTP_OK);
            $response = array('success' => true);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        } catch(Exception $e) {
            $resp->setStatusCode(Response::HTTP_FORBIDDEN);
            $response = array('failure' => true);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
    }

    /**
     * @Route("/api/notes/{id}", name="APInotesDelete", requirements={"id": "\d+"})
     * @Method({"OPTIONS", "DELETE"})
     */
    public function delNoteAction(Request $request, $id)
    {
        // CORS preflight request
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/text');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
            return $response;
        }

        $resp = new Response();
        $resp->headers->set('Content-Type', 'application/json');
        $resp->headers->set('Access-Control-Allow-Origin', '*');
        $resp->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);

        if(!$id) {
            $resp->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }

        $note = $em->getRepository('NoteBundle:Note')->find($id);
        if (!$note) {
            $resp->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error' => "note not found");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }

        try {
            $em->remove($note);
            $em->flush();
            $resp->setStatusCode(Response::HTTP_OK);
            $response = array('success' => true);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        } catch(Exception $e) {
            $resp->setStatusCode(Response::HTTP_FORBIDDEN);
            $response = array('failure' => true);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
    }

    /**
     * @Route("/api/categories", name="APIcategoriesCreate")
     * @Method({"OPTIONS", "POST"})
     */
    public function newCategoryAction(Request $request)
    {
        // CORS preflight request
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/text');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
            return $response;
        }

        $resp = new Response();
        $resp->headers->set('Content-Type', 'application/json');
        $resp->headers->set('Access-Control-Allow-Origin', '*');
        $resp->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);
        
        try {
            $label = $data['label'];
        } catch (\ErrorException $e) {
            $resp->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
        $category = new Category();
        $category->setLabel($label);

        try {
            $em->persist($category);
            $em->flush();
            $resp->setStatusCode(Response::HTTP_OK);
            $response = array('success' => true);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        } catch(Exception $e) {
            $resp->setStatusCode(Response::HTTP_FORBIDDEN);
            $response = array('failure' => true);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
    }

    /**
     * @Route("/api/categories", name="APIcategoriesUpdate")
     * @Method({"OPTIONS", "PUT"})
     */
    public function editCategoryAction(Request $request)
    {   
        // CORS preflight request
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/text');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
            return $response;
        }

        $resp = new Response();
        $resp->headers->set('Content-Type', 'application/json');
        $resp->headers->set('Access-Control-Allow-Origin', '*');
        $resp->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);
        
        try {
            $id = $data['id'];
            $label = $data['label'];
        } catch (\ErrorException $e) {
            $resp->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }

        $category = $em->getRepository('NoteBundle:Category')->find($id);
        if (!$category) {
            $resp->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error' => "category not found");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
        $category->setLabel($label);

        try {
            $em->flush();
            $resp->setStatusCode(Response::HTTP_OK);
            $response = array('success' => true);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        } catch(Exception $e) {
            $resp->setStatusCode(Response::HTTP_FORBIDDEN);
            $response = array('failure' => true);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
    }

    /**
     * @Route("/api/categories/{id}", name="APIcategoriesDelete", requirements={"id": "\d+"})
     * @Method({"OPTIONS", "DELETE"})
     */
    public function delCategoryAction(Request $request, $id)
    {
        // CORS preflight request
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/text');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
            return $response;
        }

        $resp = new Response();
        $resp->headers->set('Content-Type', 'application/json');
        $resp->headers->set('Access-Control-Allow-Origin', '*');
        $resp->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $em = $this->getDoctrine()->getManager();

        $json = $request->getContent();
        $data = json_decode($json, true);

        if(!$id) {
            $resp->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response = array('error' => "incomplete data");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }

        $category = $em->getRepository('NoteBundle:Category')->find($id);
        if (!$category) {
            $resp->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error' => "category not found");
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }

        try {
            $em->remove($category);
            $em->flush();
            $resp->setStatusCode(Response::HTTP_OK);
            $response = array('success' => true);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        } catch(Exception $e) {
            $resp->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('failure' => true);
            $jsonContent = json_encode($response);
            $resp->setContent($jsonContent);
            return $resp;
        }
    }
}

?>