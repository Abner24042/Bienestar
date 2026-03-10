Add-Type -AssemblyName System.Drawing

$srcPath = "c:\xampp\htdocs\a\Bienestar\public\assets\img\content\DSC07783.jpg"
$dstPath = "c:\xampp\htdocs\a\Bienestar\public\assets\img\content\DSC07783_opt.jpg"

$img = [System.Drawing.Image]::FromFile($srcPath)
$origW = $img.Width
$origH = $img.Height
Write-Host "Original: ${origW}x${origH}"

# Resize to 1920px wide
$maxW = 1920
$newH = [int]($origH * ($maxW / $origW))
$bmp = New-Object System.Drawing.Bitmap($maxW, $newH)
$graphics = [System.Drawing.Graphics]::FromImage($bmp)
$graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
$graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
$graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
$graphics.DrawImage($img, 0, 0, $maxW, $newH)

# Save with JPEG quality 70
$jpegCodec = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.MimeType -eq "image/jpeg" }
$encoderParams = New-Object System.Drawing.Imaging.EncoderParameters(1)
$encoderParams.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality, 70L)
$bmp.Save($dstPath, $jpegCodec, $encoderParams)

$graphics.Dispose()
$bmp.Dispose()
$img.Dispose()

$newSize = [math]::Round((Get-Item $dstPath).Length / 1KB)
Write-Host "Optimized: ${maxW}x${newH}, ${newSize}KB"

# Replace original
Remove-Item $srcPath
Rename-Item $dstPath "DSC07783.jpg"
Write-Host "Done!"
