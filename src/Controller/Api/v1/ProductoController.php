<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use App\Entity\Producto;
use Doctrine\ORM\EntityManagerInterface;


class testController extends AbstractController
{

    //? Los nombres y rutas son descriptivos para identificar fácil los endpoints. En buenas prácticas no los utilzaria.

    private $logger;

    public function __construct(LoggerInterface $logger) //Implementar inferfaz
    {
        $this->logger = $logger;
    }

     /**
     * @Route("/agregar-productos", name="agregar_productos", methods={"POST"})
     */
    public function agregarProductos(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Obtiene los datos del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true);

        //Estoy en Argentina sino esto no se hace.
        $timezone = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $now = new \DateTime('now', $timezone);

        // Verifico si el SKU ya existe
        $existingProduct = $entityManager->getRepository(Producto::class)->findOneBy(['sku' => $data['sku']]);

        if ($existingProduct === null) {
            $now = new \DateTime('now');

            // Creamos nuevo producto
            $producto = new Producto();
            $producto->setSku($data['sku']);
            $producto->setNombreProducto($data['nombre_producto']);
            $producto->setDescripcion($data['descripcion']);
            $createdAt = new \DateTime($data['created_at'], $timezone);
            $producto->setCreatedAt($createdAt);;

            // Persiste el producto
            $entityManager->persist($producto);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Datos agregados correctamente a la tabla productos.']);
        } else {
            return new JsonResponse(['message' => 'Ya existe un SKU con esos datos.'], 400);
        }
    }

    /**
     * @Route("/eliminar-producto", name="eliminar_producto", methods={"DELETE"})
     */
    public function eliminarProducto(EntityManagerInterface $entityManager, Request $request): Response
    {
        $ids = $request->query->get('id', '');

        //Separo la lista (en caso de venir muchos ids por ,)
        $ids = explode(',', $ids);

        // Obtengo el o los resultados
        $productos = $entityManager->getRepository(Producto::class)->findBy(['id' => $ids]);

        foreach ($productos as $producto) {
            if ($producto !== null) {
                $entityManager->remove($producto);
            }
        }
        $entityManager->flush();

        return new Response("Productos con ID(s) '" . implode(', ', $ids) . "' eliminados correctamente.");
    }


    /**
     * @Route("/editar-producto/{sku}", name="editar_producto", methods={"POST", "PUT"})
     */
    public function editarProducto(EntityManagerInterface $entityManager, Request $request, $sku): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $timezone = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $now = new \DateTime('now', $timezone);

        $producto = $entityManager->getRepository(Producto::class)->findOneBy(['sku' => $sku]);

        if ($producto !== null) {
            if (isset($data['nombre_producto'])) {
                $producto->setNombreProducto($data['nombre_producto']);
            }

            if (isset($data['descripcion'])) {
                $producto->setDescripcion($data['descripcion']);
            }

            $producto->setUpdatedAt($now);

            $entityManager->flush();

            $response = [
                'message' => "Producto con SKU '{$sku}' editado correctamente.",
                'data' => [
                    'nombre_producto' => $producto->getNombreProducto(),
                    'descripcion' => $producto->getDescripcion(),
                    'updated_at' => $producto->getUpdatedAt()->format('Y-m-d H:i:s'),
                ],
            ];

            return new JsonResponse($response);
        } else {
            return new JsonResponse(['message' => "No se encontró un producto con SKU '{$sku}'."], 404);
        }
    }

    /**
     * @Route("/productos", name="listar_productos")
     */
    public function listarProductos(EntityManagerInterface $entityManager): JsonResponse
    {
        $productos = $entityManager->getRepository(Producto::class)->findAll();

        $data = [];
        foreach ($productos as $producto) {
            //Verificacion de fechas por si es null y por si existe
            $updatedAt = $producto->getUpdatedAt();

            $formattedUpdatedAt = $updatedAt ? $updatedAt->format('Y-m-d H:i:s') : null;

            $data[] = [
                'id' => $producto->getId(),
                'sku' => $producto->getSku(),
                'nombre_producto' => $producto->getNombreProducto(),
                'descripcion' => $producto->getDescripcion(),
                'created_at' => $producto->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $formattedUpdatedAt,
            ];
        }

        return new JsonResponse($data);
    }

}