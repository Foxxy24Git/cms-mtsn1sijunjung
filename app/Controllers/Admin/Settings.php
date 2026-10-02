<?php

namespace App\Controllers\Admin;

use App\Models\SettingModel;

class Settings extends BaseController
{
    protected $settingModel;

    /**
     * Initialize controller with the SettingModel.
     *
     * @param \CodeIgniter\HTTP\RequestInterface   $request
     * @param \CodeIgniter\HTTP\ResponseInterface  $response
     * @param \Psr\Log\LoggerInterface             $logger
     */
    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->settingModel = new SettingModel();
    }

    /**
     * Display the settings management page.
     *
     * @return string
     */
    public function index()
    {
        $keys = [
            'site_name', 'site_tagline', 'site_description',
            'site_logo_text', 'site_logo_icon', 'school_logo',
            'contact_phone', 'contact_email', 'contact_address', 'contact_hours',
            'contact_latitude', 'contact_longitude',
            'social_facebook', 'social_instagram', 'social_youtube', 'social_tiktok',
            'hero_badge', 'hero_title', 'hero_subtitle',
            'hero_btn_primary_text', 'hero_btn_primary_url',
            'hero_btn_secondary_text', 'hero_btn_secondary_url',
            'spmb_url',
            'footer_description', 'footer_copyright', 'footer_services', 'footer_links',
            'theme_color',
            'counter_stats',
            'hero_stats',
            'about',
            'principal',
            'section_settings',
            'page_banners',
        ];

        $this->data['title']    = 'Pengaturan';
        $this->data['settings'] = $this->settingModel->getMany($keys);

        return view('admin/settings', $this->data);
    }

    /**
     * Save all settings from the settings form.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function save()
    {
        $keys = [
            'site_name', 'site_tagline', 'site_description',
            'site_logo_text', 'site_logo_icon',
            'contact_phone', 'contact_email', 'contact_address', 'contact_hours',
            'contact_latitude', 'contact_longitude',
            'social_facebook', 'social_instagram', 'social_youtube', 'social_tiktok',
            'hero_badge', 'hero_title', 'hero_subtitle',
            'hero_btn_primary_text', 'hero_btn_primary_url',
            'hero_btn_secondary_text', 'hero_btn_secondary_url',
            'spmb_url',
            'footer_description', 'footer_copyright',
            'theme_color',
        ];

        foreach (['counter_stats', 'hero_stats', 'section_settings', 'page_banners', 'footer_services', 'footer_links'] as $jsonKey) {
            $val = $this->request->getPost($jsonKey);
            if (is_array($val)) {
                $this->settingModel->setSetting($jsonKey, json_encode($val));
            }
        }

        $about = $this->request->getPost('about');
        if (is_array($about)) {
            $currentImage = $about['current_image'] ?? '';
            unset($about['current_image']);

            $file = $this->request->getFile('about_image');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $uploaded = $this->uploadAboutImage($file, $currentImage);
                if ($uploaded) {
                    $about['image'] = $uploaded;
                } else {
                    $about['image'] = $currentImage;
                }
            } else {
                $about['image'] = $currentImage;
            }

            if (isset($about['highlights_text'])) {
                $lines = array_map('trim', explode("\n", $about['highlights_text']));
                $about['highlights'] = array_values(array_filter($lines));
                unset($about['highlights_text']);
            }

            // Hero Slider Image Handling
            for ($i = 1; $i <= 3; $i++) {
                $currKey = 'current_hero_slider_' . $i;
                $currentSlider = $about[$currKey] ?? '';
                unset($about[$currKey]);

                $removeSlider = $this->request->getPost('remove_hero_slider_' . $i);
                
                if ($removeSlider) {
                    if ($currentSlider && str_starts_with($currentSlider, base_url('uploads/hero/'))) {
                        $oldPath = FCPATH . 'uploads/hero/' . basename($currentSlider);
                        if (is_file($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    $about['hero_slider_' . $i] = '';
                } else {
                    $fileSlider = $this->request->getFile('hero_slider_image_' . $i);
                    if ($fileSlider && $fileSlider->isValid() && !$fileSlider->hasMoved()) {
                        $uploadedSlider = $this->uploadHeroSliderImage($fileSlider, $currentSlider);
                        if ($uploadedSlider) {
                            $about['hero_slider_' . $i] = $uploadedSlider;
                        } else {
                            $about['hero_slider_' . $i] = $currentSlider;
                        }
                    } else {
                        $about['hero_slider_' . $i] = $currentSlider;
                    }
                }
            }

            // School logo handling
            $currentLogo = $about['current_logo'] ?? ($this->settingModel->get('school_logo')['value'] ?? '');
            unset($about['current_logo']);
            $removeLogo = $this->request->getPost('remove_school_logo');

            if ($removeLogo) {
                if ($currentLogo && str_starts_with($currentLogo, base_url('uploads/logo/'))) {
                    $oldPath = FCPATH . 'uploads/logo/' . basename($currentLogo);
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $about['logo'] = '';
                $this->settingModel->setSetting('school_logo', '');
            } else {
                $fileLogo = $this->request->getFile('school_logo');
                if ($fileLogo && $fileLogo->isValid() && !$fileLogo->hasMoved()) {
                    $uploadedLogo = $this->uploadSchoolLogo($fileLogo, $currentLogo);
                    if ($uploadedLogo) {
                        $about['logo'] = $uploadedLogo;
                        $this->settingModel->setSetting('school_logo', $uploadedLogo);
                    } else {
                        $about['logo'] = $currentLogo;
                        $this->settingModel->setSetting('school_logo', $currentLogo);
                    }
                } else {
                    $about['logo'] = $currentLogo;
                    $this->settingModel->setSetting('school_logo', $currentLogo);
                }
            }

            $this->settingModel->setSetting('about', json_encode($about));
        }

        $principal = $this->request->getPost('principal');
        if (is_array($principal)) {
            $currentImage = $principal['current_image'] ?? '';
            unset($principal['current_image']);

            $file = $this->request->getFile('principal_photo');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $uploaded = $this->uploadPrincipalPhoto($file, $currentImage);
                if ($uploaded) {
                    $principal['photo'] = $uploaded;
                } else {
                    $principal['photo'] = $currentImage;
                }
            } else {
                $principal['photo'] = $currentImage;
            }

            $this->settingModel->setSetting('principal', json_encode($principal));
        }

        foreach ($keys as $key) {
            $value = $this->request->getPost($key);
            if ($value !== null) {
                $this->settingModel->setSetting($key, $value);
            }
        }

        service('cache')->clean();

        return redirect()->to('/admin/settings')->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Upload and resize the about section image.
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile $file
     * @param string                               $currentImage
     *
     * @return string|null
     */
    private function uploadAboutImage($file, string $currentImage): ?string
    {
        $uploadPath = FCPATH . 'uploads/about';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $tempPath = $file->getTempName();

        $srcInfo = @getimagesize($tempPath);
        if ($srcInfo === false || !in_array($srcInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return null;
        }

        [$origWidth, $origHeight, $type] = $srcInfo;
        $newName = $this->buildSafeName($type);
        $maxWidth = 1200;

        if ($origWidth > $maxWidth) {
            $ratio = $maxWidth / $origWidth;
            $newWidth = $maxWidth;
            $newHeight = (int) round($origHeight * $ratio);

            $srcImage = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($tempPath),
                IMAGETYPE_PNG  => imagecreatefrompng($tempPath),
                IMAGETYPE_WEBP => imagecreatefromwebp($tempPath),
                default        => null,
            };

            if (!$srcImage) {
                return null;
            }

            $dstImage = imagecreatetruecolor($newWidth, $newHeight);

            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
            }

            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            $destPath = $uploadPath . '/' . $newName;
            match ($type) {
                IMAGETYPE_JPEG => imagejpeg($dstImage, $destPath, 85),
                IMAGETYPE_PNG  => imagepng($dstImage, $destPath, 8),
                IMAGETYPE_WEBP => imagewebp($dstImage, $destPath, 85),
                default        => null,
            };

            imagedestroy($srcImage);
            imagedestroy($dstImage);
        } else {
            $file->move($uploadPath, $newName);
        }

        if ($currentImage && str_starts_with($currentImage, base_url('uploads/about/'))) {
            $oldPath = $uploadPath . '/' . basename($currentImage);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return base_url('uploads/about/' . $newName);
    }

    /**
     * Upload and resize the principal photo.
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile $file
     * @param string                               $currentImage
     *
     * @return string|null
     */
    private function uploadPrincipalPhoto($file, string $currentImage): ?string
    {
        $uploadPath = FCPATH . 'uploads/principal';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $tempPath = $file->getTempName();

        $srcInfo = @getimagesize($tempPath);
        if ($srcInfo === false || !in_array($srcInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return null;
        }

        [$origWidth, $origHeight, $type] = $srcInfo;
        $newName = $this->buildSafeName($type);
        $maxWidth = 600;

        if ($origWidth > $maxWidth) {
            $ratio = $maxWidth / $origWidth;
            $newWidth = $maxWidth;
            $newHeight = (int) round($origHeight * $ratio);

            $srcImage = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($tempPath),
                IMAGETYPE_PNG  => imagecreatefrompng($tempPath),
                IMAGETYPE_WEBP => imagecreatefromwebp($tempPath),
                default        => null,
            };

            if (!$srcImage) {
                return null;
            }

            $dstImage = imagecreatetruecolor($newWidth, $newHeight);

            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
            }

            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            $destPath = $uploadPath . '/' . $newName;
            match ($type) {
                IMAGETYPE_JPEG => imagejpeg($dstImage, $destPath, 85),
                IMAGETYPE_PNG  => imagepng($dstImage, $destPath, 8),
                IMAGETYPE_WEBP => imagewebp($dstImage, $destPath, 85),
                default        => null,
            };

            imagedestroy($srcImage);
            imagedestroy($dstImage);
        } else {
            $file->move($uploadPath, $newName);
        }

        if ($currentImage && str_starts_with($currentImage, base_url('uploads/principal/'))) {
            $oldPath = $uploadPath . '/' . basename($currentImage);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return base_url('uploads/principal/' . $newName);
    }

    /**
     * Upload and process the school logo.
     * Supports SVG, PNG, JPEG, and WebP, preserving PNG transparency.
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile $file
     * @param string                               $currentLogo
     *
     * @return string|null
     */
    private function uploadSchoolLogo($file, string $currentLogo): ?string
    {
        $uploadPath = FCPATH . 'uploads/logo';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $tempPath = $file->getTempName();
        $clientExt = strtolower(pathinfo($file->getClientName(), PATHINFO_EXTENSION));

        // SVG support
        if ($clientExt === 'svg' || $file->getMimeType() === 'image/svg+xml') {
            $newName = time() . '_' . bin2hex(random_bytes(8)) . '.svg';
            $file->move($uploadPath, $newName);

            if ($currentLogo && str_starts_with($currentLogo, base_url('uploads/logo/'))) {
                $oldPath = $uploadPath . '/' . basename($currentLogo);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            return base_url('uploads/logo/' . $newName);
        }

        $srcInfo = @getimagesize($tempPath);
        if ($srcInfo === false || !in_array($srcInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return null;
        }

        [$origWidth, $origHeight, $type] = $srcInfo;
        $newName = $this->buildSafeName($type);
        $maxDimension = 500;

        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            if ($origWidth >= $origHeight) {
                $ratio = $maxDimension / $origWidth;
                $newWidth = $maxDimension;
                $newHeight = (int) round($origHeight * $ratio);
            } else {
                $ratio = $maxDimension / $origHeight;
                $newHeight = $maxDimension;
                $newWidth = (int) round($origWidth * $ratio);
            }

            $srcImage = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($tempPath),
                IMAGETYPE_PNG  => imagecreatefrompng($tempPath),
                IMAGETYPE_WEBP => imagecreatefromwebp($tempPath),
                default        => null,
            };

            if (!$srcImage) {
                return null;
            }

            $dstImage = imagecreatetruecolor($newWidth, $newHeight);

            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
                $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            $destPath = $uploadPath . '/' . $newName;
            match ($type) {
                IMAGETYPE_JPEG => imagejpeg($dstImage, $destPath, 90),
                IMAGETYPE_PNG  => imagepng($dstImage, $destPath, 8),
                IMAGETYPE_WEBP => imagewebp($dstImage, $destPath, 90),
                default        => null,
            };

            imagedestroy($srcImage);
            imagedestroy($dstImage);
        } else {
            $file->move($uploadPath, $newName);
        }

        if ($currentLogo && str_starts_with($currentLogo, base_url('uploads/logo/'))) {
            $oldPath = $uploadPath . '/' . basename($currentLogo);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return base_url('uploads/logo/' . $newName);
    }

    /**
     * Upload and resize the hero slider image.
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile $file
     * @param string                               $currentImage
     *
     * @return string|null
     */
    private function uploadHeroSliderImage($file, string $currentImage): ?string
    {
        $uploadPath = FCPATH . 'uploads/hero';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $tempPath = $file->getTempName();

        $srcInfo = @getimagesize($tempPath);
        if ($srcInfo === false || !in_array($srcInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return null;
        }

        [$origWidth, $origHeight, $type] = $srcInfo;
        $newName = $this->buildSafeName($type);
        $maxWidth = 1920;

        if ($origWidth > $maxWidth) {
            $ratio = $maxWidth / $origWidth;
            $newWidth = $maxWidth;
            $newHeight = (int) round($origHeight * $ratio);

            $srcImage = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($tempPath),
                IMAGETYPE_PNG  => imagecreatefrompng($tempPath),
                IMAGETYPE_WEBP => imagecreatefromwebp($tempPath),
                default        => null,
            };

            if (!$srcImage) {
                return null;
            }

            $dstImage = imagecreatetruecolor($newWidth, $newHeight);

            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
            }

            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            $destPath = $uploadPath . '/' . $newName;
            match ($type) {
                IMAGETYPE_JPEG => imagejpeg($dstImage, $destPath, 85),
                IMAGETYPE_PNG  => imagepng($dstImage, $destPath, 8),
                IMAGETYPE_WEBP => imagewebp($dstImage, $destPath, 85),
                default        => null,
            };

            imagedestroy($srcImage);
            imagedestroy($dstImage);
        } else {
            $file->move($uploadPath, $newName);
        }

        if ($currentImage && str_starts_with($currentImage, base_url('uploads/hero/'))) {
            $oldPath = $uploadPath . '/' . basename($currentImage);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return base_url('uploads/hero/' . $newName);
    }
}
