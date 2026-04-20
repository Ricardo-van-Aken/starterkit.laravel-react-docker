variable "IMAGE_REPO" {}
variable "TAG_ADDITION" {}
variable "LARAVEL_VARIANT" {}

group "default" {
  targets = ["app", "scheduler", "nginx", "redis", "certbot"]
}

target "app" {
  target = "${LARAVEL_VARIANT}"
  tags = ["${IMAGE_REPO}:laravel-app.${TAG_ADDITION}"]
  cache-from = [
    "type=registry,ref=${IMAGE_REPO}:laravel-app.${TAG_ADDITION}.deploy-cache",
    "type=registry,ref=${IMAGE_REPO}:laravel-app.main.cache"
  ]
  cache-to = ["type=registry,ref=${IMAGE_REPO}:laravel-app.${TAG_ADDITION}.deploy-cache,mode=max"]
}

target "scheduler" {
  target = "scheduler"
  args = {
    SCHEDULER_BASE = "${LARAVEL_VARIANT}"
  }
  tags = ["${IMAGE_REPO}:laravel-scheduler.${TAG_ADDITION}"]
  cache-from = [
    "type=registry,ref=${IMAGE_REPO}:laravel-scheduler.${TAG_ADDITION}.deploy-cache",
    "type=registry,ref=${IMAGE_REPO}:laravel-scheduler.main.cache"
  ]
  cache-to = ["type=registry,ref=${IMAGE_REPO}:laravel-scheduler.${TAG_ADDITION}.deploy-cache,mode=max"]
}

target "nginx" {
  tags = ["${IMAGE_REPO}:website-nginx.${TAG_ADDITION}"]
  cache-from = [
    "type=registry,ref=${IMAGE_REPO}:website-nginx.${TAG_ADDITION}.deploy-cache",
    "type=registry,ref=${IMAGE_REPO}:website-nginx.main.cache"
  ]
  cache-to = ["type=registry,ref=${IMAGE_REPO}:website-nginx.${TAG_ADDITION}.deploy-cache,mode=max"]
}

target "redis" {
  tags = ["${IMAGE_REPO}:website-redis.${TAG_ADDITION}"]
  cache-from = [
    "type=registry,ref=${IMAGE_REPO}:website-redis.${TAG_ADDITION}.deploy-cache",
    "type=registry,ref=${IMAGE_REPO}:website-redis.main.cache"
  ]
  cache-to = ["type=registry,ref=${IMAGE_REPO}:website-redis.${TAG_ADDITION}.deploy-cache,mode=max"]
}

target "certbot" {
  tags = ["${IMAGE_REPO}:website-certbot.${TAG_ADDITION}"]
  cache-from = [
    "type=registry,ref=${IMAGE_REPO}:website-certbot.${TAG_ADDITION}.deploy-cache",
    "type=registry,ref=${IMAGE_REPO}:website-certbot.main.cache"
  ]
  cache-to = ["type=registry,ref=${IMAGE_REPO}:website-certbot.${TAG_ADDITION}.deploy-cache,mode=max"]
}
