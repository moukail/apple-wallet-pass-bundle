<?php

namespace Moukail\AppleWalletPassBundle\Controller;

use Moukail\AppleWalletPassBundle\PassServiceInterface;
use Moukail\AppleWalletPassBundle\Repository\PassRepositoryInterface;
use Psr\Log\LoggerInterface;
use Moukail\AppleWalletPassBundle\Entity\Device;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DeviceController extends AbstractController
{
    /**
     * @var PassRepositoryInterface
     */
    private PassRepositoryInterface $passRepository;

    /**
     * @var PassServiceInterface
     */
    private PassServiceInterface $passService;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * DeviceController constructor.
     * @param PassRepositoryInterface $passRepository
     * @param PassServiceInterface $passService
     * @param LoggerInterface $logger
     */
    public function __construct(PassRepositoryInterface $passRepository, PassServiceInterface $passService, LoggerInterface $logger)
    {
        $this->passRepository = $passRepository;
        $this->passService = $passService;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param $deviceLibraryIdentifier
     * @param $passTypeIdentifier
     * @param string $passId
     * @return JsonResponse
     */
    public function deviceRegistration(Request $request, $deviceLibraryIdentifier, $passTypeIdentifier, string $passId): JsonResponse
    {
        $pass = $this->passRepository->find($passId);

        if (!$pass){
            return $this->json([
                'status' => 'error',
                'message' => 'error',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $this->logger->alert('DeviceController:deviceRegistration', [
            'deviceLibraryIdentifier' => $deviceLibraryIdentifier,
            'passTypeIdentifier' => $passTypeIdentifier,
            'serialNumber' => $pass->getId(),
            'user_agent' => $request->headers->get('User-Agent'),
            'data' => $data,
        ]);

        $entityManager = $this->getDoctrine()->getManager();

        $detect = new \Mobile_Detect();
        $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

        $deviceOs = 'no';
        // Check for a specific platform with the help of the magic methods:
        if ($detect->isiOS()) {
            $deviceOs = Device::OS_IOS;
        }

        if ($detect->isAndroidOS()) {
            $deviceOs = Device::OS_ANDROID;
        }

        $device = new Device();

        $device->setPass($pass)
            ->setType($deviceType)
            ->setOs($deviceOs)
            ->setDeviceLibraryIdentifier($deviceLibraryIdentifier)
            ->setPushToken($data['pushToken']);

        $entityManager->persist($device);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'success',
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param $deviceLibraryIdentifier
     * @param $passTypeIdentifier
     * @param string $passId
     * @return JsonResponse
     */
    public function deviceRegistrationAttido(Request $request, $deviceLibraryIdentifier, $passTypeIdentifier, string $passId): JsonResponse
    {
        $pass = $this->passRepository->find($passId);

        if (!$pass){
            return $this->json([
                'status' => 'error',
                'message' => 'error',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $this->logger->alert('ApplePassApiController:deviceRegistrationAttido', [
            'deviceLibraryIdentifier' => $deviceLibraryIdentifier,
            'passTypeIdentifier' => $passTypeIdentifier,
            'serialNumber' => $pass->getId(),
            'data' => $data,
        ]);

        return $this->json([
            'status' => 'success',
            'message' => 'success',
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param $deviceLibraryIdentifier
     * @param $passTypeIdentifier
     * @param string $passId
     * @return JsonResponse
     */
    public function deviceUnregistration(Request $request, $deviceLibraryIdentifier, $passTypeIdentifier, string $passId): JsonResponse
    {
        $pass = $this->passRepository->find($passId);

        if (!$pass){
            return $this->json([
                'status' => 'error',
                'message' => 'error',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->logger->alert('ApplePassApiController:deviceUnregistration', [
            'deviceLibraryIdentifier' => $deviceLibraryIdentifier,
            'passTypeIdentifier' => $passTypeIdentifier,
            'serialNumber' => $pass->getId(),
        ]);

        $entityManager = $this->getDoctrine()->getManager();

        $device = $entityManager->getRepository(Device::class)->findOneBy(['deviceLibraryIdentifier' => $deviceLibraryIdentifier]);
        if (!$device) {
            return $this->json([
                'status' => 'error',
                'message' => 'error',
            ], JsonResponse::HTTP_OK);
        }

        $entityManager->remove($device);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'success',
        ], JsonResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param $deviceLibraryIdentifier
     * @param $passTypeIdentifier
     * @return JsonResponse
     */
    public function deviceSerialNumbers(Request $request, $deviceLibraryIdentifier, $passTypeIdentifier): JsonResponse
    {
        $this->logger->alert('ApplePassApiController:deviceSerialNumbers', [
            'deviceLibraryIdentifier' => $deviceLibraryIdentifier,
            'passTypeIdentifier' => $passTypeIdentifier,
            'request' => $request->request->all(),
            'query' => $request->query->all(),
        ]);

        /** @var Device $device */
        $device = $this->getDoctrine()->getRepository(Device::class)->findOneBy(['deviceLibraryIdentifier' => $deviceLibraryIdentifier]);

        if (!$device) {
            return $this->json([], JsonResponse::HTTP_NO_CONTENT);
        }

        $pass = $device->getPass();

        return $this->json([
            'serialNumbers' => [$pass->getId()],
            'lastUpdated' => $pass->getCreatedAt()->format('d-m-Y H:i:s'),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param PassServiceInterface $passService
     * @param $passTypeIdentifier
     * @param string $passId
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function lastVersionPass(Request $request, $passTypeIdentifier, string $passId): BinaryFileResponse
    {
        $pass = $this->passRepository->find($passId);

        if (!$pass){
            throw new \Exception('can not make pass');
        }

        $response = $this->passService->makeWalletPass($pass);

        $this->logger->alert('ApplePassApiController:lastVersionPass', [
            'passTypeIdentifier' => $passTypeIdentifier,
            'serialNumber' => $pass->getId(),
            'user_agent' => $request->headers->get('User-Agent'),
            'headers' => $request->headers,
        ]);

        return $response;
    }
}
