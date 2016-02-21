<?php

/*
 * This file is part of the BenGorFile library.
 *
 * (c) Beñat Espiña <benatespina@gmail.com>
 * (c) Gorka Laucirica <gorka.lauzirika@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BenGor\File\Application\Service;

use BenGor\File\Domain\Model\FileException;
use BenGor\File\Domain\Model\FileExtension;
use BenGor\File\Domain\Model\FileName;
use BenGor\File\Domain\Model\FileRepository;
use BenGor\File\Domain\Model\Filesystem;
use BenGor\File\Domain\Model\UploadedFileException;
use Ddd\Application\Service\ApplicationService;

/**
 * Overwrite file service class.
 *
 * @author Beñat Espiña <benatespina@gmail.com>
 */
final class OverwriteFileService implements ApplicationService
{
    /**
     * The filesystem.
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * The file repository.
     *
     * @var FileRepository
     */
    private $repository;

    /**
     * Constructor.
     *
     * @param Filesystem     $filesystem  The filesystem
     * @param FileRepository $aRepository THhe file repository
     */
    public function __construct(Filesystem $filesystem, FileRepository $aRepository)
    {
        $this->filesystem = $filesystem;
        $this->repository = $aRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param OverwriteFileRequest $request The override file request
     */
    public function execute($request = null)
    {
        $uploadedFile = $request->uploadedFile();
        $name = new FileName($request->name());
        $extension = new FileExtension($uploadedFile->extension());

        if (false === $this->filesystem->has($name, $extension)) {
            throw UploadedFileException::doesNotExist($name, $extension);
        }
        $file = $this->repository->fileOfName($name, $extension);
        if (null === $file) {
            throw FileException::doesNotExist($name, $extension);
        }

        $this->filesystem->overwrite($name, $extension, $uploadedFile->content());
        $file->overwrite($name, $extension);

        $this->repository->persist($file);

        return new OverwriteFileResponse($file);
    }
}
