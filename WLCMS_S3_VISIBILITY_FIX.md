# WLCMS S3 Upload Fix - Remove ACL Visibility Parameter

## Issue
WLCMS MediaController fails to upload to S3 when buckets have ACLs disabled (AWS best practice since 2023).

**Error:** Upload returns `false` when using `'visibility' => 'public'` parameter, causing thumbnail generation to fail with empty file paths.

## Root Cause
Modern S3 buckets use "Bucket owner enforced" object ownership, which disables ACLs. When WLCMS tries to set file ACLs via the visibility parameter, S3 silently rejects the upload.

## Solution
Remove the `'visibility' => 'public'` parameter from S3 uploads. Use bucket policies for public access instead.

## Required Changes

### File: `vendor/westlinks/wlcms/src/Http/Controllers/Admin/MediaController.php`

**Change 1: Line ~269 in `upload()` method**

Replace:
```php
$path = $file->storeAs($directory, $filename, [
    'disk' => $disk,
    'visibility' => 'public'
]);
```

With:
```php
$path = $file->storeAs($directory, $filename, $disk);
```

**Change 2: Line ~469 in `generateThumbnails()` method**

Replace:
```php
Storage::disk($disk)->put($thumbnailPath, (string) $encoded, 'public');
```

With:
```php
Storage::disk($disk)->put($thumbnailPath, (string) $encoded);
```

## Configuration Required

Add this bucket policy to make uploaded files publicly readable:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PublicReadGetObject",
      "Effect": "Allow",
      "Principal": "*",
      "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::YOUR-BUCKET-NAME/*"
    }
  ]
}
```

## Benefits
- ✅ Complies with AWS best practices (2023+)
- ✅ Works with ACL-disabled buckets
- ✅ Simpler permission management
- ✅ Better CloudFront/CDN compatibility
- ✅ Future-proof as AWS deprecates ACLs

## Testing
After changes:
1. Upload an image through WLCMS media library
2. Verify file appears in S3
3. Verify thumbnails are generated
4. Verify public URL is accessible

## Backward Compatibility
Still works with legacy ACL-enabled buckets. The bucket policy approach is transparent to the application.
