# Remote Storage

This guide covers configuring S3-compatible remote storage for your application. **Most deployments don't need this** - local storage works fine for single-server setups.

## When You Need Remote Storage

Consider remote storage when:

- **Multi-server deployments** - Queue workers run on separate machines and need access to uploaded files
- **Horizontal scaling** - Multiple application servers sharing storage
- **High availability** - Storage persists independently of application servers

For single-server deployments, local storage is simpler and works reliably.

## Storage Architecture

The application uses two separate storage buckets:

| Bucket | Purpose | Visibility |
|--------|---------|------------|
| Private | Imports, exports, temp files, backups | Private (signed URLs) |
| Public | User uploads, avatars, public assets | Public (direct access) |

Each bucket can have its own credentials, allowing different IAM policies or even different providers (e.g., private on AWS, public on Cloudflare R2).

## Configuration

### 1. Set Storage Mode

In your `.env` file:

```env
STORAGE_MODE=s3
```

This single setting switches all operation's default disk to use S3.

### 2. Configure Private Bucket

The private bucket stores sensitive data like import/export files:

```env
S3_PRIVATE_ACCESS_KEY_ID=your-private-key
S3_PRIVATE_SECRET_ACCESS_KEY=your-private-secret
S3_PRIVATE_DEFAULT_REGION=us-east-1
S3_PRIVATE_BUCKET=your-app-private
```

### 3. Configure Public Bucket

The public bucket stores user-facing files:

```env
S3_PUBLIC_ACCESS_KEY_ID=your-public-key
S3_PUBLIC_SECRET_ACCESS_KEY=your-public-secret
S3_PUBLIC_DEFAULT_REGION=us-east-1
S3_PUBLIC_BUCKET=your-app-public
S3_PUBLIC_URL=https://cdn.example.com
```

Set `S3_PUBLIC_URL` to your CDN URL if using CloudFront or similar.

## S3-Compatible Providers

The configuration supports any S3-compatible service by setting custom endpoints.

### DigitalOcean Spaces

```env
# Private bucket
S3_PRIVATE_ACCESS_KEY_ID=your-spaces-key
S3_PRIVATE_SECRET_ACCESS_KEY=your-spaces-secret
S3_PRIVATE_DEFAULT_REGION=nyc3
S3_PRIVATE_BUCKET=your-private-space
S3_PRIVATE_ENDPOINT=https://nyc3.digitaloceanspaces.com

# Public bucket
S3_PUBLIC_ACCESS_KEY_ID=your-spaces-key
S3_PUBLIC_SECRET_ACCESS_KEY=your-spaces-secret
S3_PUBLIC_DEFAULT_REGION=nyc3
S3_PUBLIC_BUCKET=your-public-space
S3_PUBLIC_ENDPOINT=https://nyc3.digitaloceanspaces.com
S3_PUBLIC_URL=https://your-public-space.nyc3.cdn.digitaloceanspaces.com
```

### Cloudflare R2

```env
# Private bucket
S3_PRIVATE_ACCESS_KEY_ID=your-r2-key
S3_PRIVATE_SECRET_ACCESS_KEY=your-r2-secret
S3_PRIVATE_BUCKET=your-private-bucket
S3_PRIVATE_ENDPOINT=https://your-account-id.r2.cloudflarestorage.com

# Public bucket
S3_PUBLIC_ACCESS_KEY_ID=your-r2-key
S3_PUBLIC_SECRET_ACCESS_KEY=your-r2-secret
S3_PUBLIC_BUCKET=your-public-bucket
S3_PUBLIC_ENDPOINT=https://your-account-id.r2.cloudflarestorage.com
S3_PUBLIC_URL=https://your-public-bucket.your-domain.com
```

R2 doesn't require a region - leave `S3_*_DEFAULT_REGION` unset or use `auto`.

### MinIO (Self-Hosted)

```env
# Private bucket
S3_PRIVATE_ACCESS_KEY_ID=minioadmin
S3_PRIVATE_SECRET_ACCESS_KEY=minioadmin
S3_PRIVATE_BUCKET=private
S3_PRIVATE_ENDPOINT=http://minio:9000
S3_PRIVATE_USE_PATH_STYLE_ENDPOINT=true

# Public bucket
S3_PUBLIC_ACCESS_KEY_ID=minioadmin
S3_PUBLIC_SECRET_ACCESS_KEY=minioadmin
S3_PUBLIC_BUCKET=public
S3_PUBLIC_ENDPOINT=http://minio:9000
S3_PUBLIC_URL=http://localhost:9000/public
S3_PUBLIC_USE_PATH_STYLE_ENDPOINT=true
```

MinIO requires `USE_PATH_STYLE_ENDPOINT=true`.

## AWS S3 Setup

### Create Buckets

1. Create two S3 buckets (e.g., `myapp-private` and `myapp-public`)
2. For the public bucket, enable public access and set a bucket policy:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::myapp-public/*"
        }
    ]
}
```

### Create IAM Users

Create separate IAM users for each bucket with minimal permissions:

**Private bucket policy:**
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:GetObject",
                "s3:PutObject",
                "s3:DeleteObject",
                "s3:ListBucket"
            ],
            "Resource": [
                "arn:aws:s3:::myapp-private",
                "arn:aws:s3:::myapp-private/*"
            ]
        }
    ]
}
```

**Public bucket policy:**
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:GetObject",
                "s3:PutObject",
                "s3:DeleteObject",
                "s3:ListBucket"
            ],
            "Resource": [
                "arn:aws:s3:::myapp-public",
                "arn:aws:s3:::myapp-public/*"
            ]
        }
    ]
}
```

## Migration Guidance

> **Note:** Data migration is not officially supported. The following is provided as general guidance only.

If you have existing files and need to migrate to remote storage, you generally just need to copy all of the files to your destination with the same folder structure.

1. **Copy existing files** to your S3 buckets:
   ```bash
   # Copy private files
   aws s3 sync storage/app/private s3://myapp-private

   # Copy public files
   aws s3 sync storage/app/public s3://myapp-public
   ```

2. **Update environment** and clear config cache:
   ```bash
   php artisan config:clear
   ```

3. **Verify functionality** by testing file uploads and downloads

## Troubleshooting

### Permission Denied Errors

- Verify IAM credentials are correct
- Check bucket policy allows required actions
- Ensure bucket region matches configuration

### CORS Errors (Public Bucket)

Configure CORS for direct browser uploads:

```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "PUT", "POST"],
        "AllowedOrigins": ["https://your-domain.com"],
        "ExposeHeaders": ["ETag"]
    }
]
```

### Connection Timeouts

- Verify endpoint URL is correct
- Check network/firewall allows S3 connections
- For MinIO, ensure the service is running

### Files Not Visible

- Check visibility settings on uploaded files
- Verify public bucket has correct bucket policy
- Check `S3_PUBLIC_URL` is set correctly for CDN
