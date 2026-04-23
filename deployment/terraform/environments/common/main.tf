module "bucket" {
  source     = "../../modules/bucket"
  project_id = var.project_id
}

module "droplet" {
  source       = "../../modules/droplet"
  project_id   = var.project_id
  ssh_key_name = var.ssh_key_name
}

module "record_root" {
  source    = "../../modules/record"
  domain_id = var.domain_id
  value     = module.droplet.ipv4
  type      = "A"
  name      = var.record_name
}

module "record_cname" {
  count     = terraform.workspace == "production" ? 1 : 0
  source    = "../../modules/record"
  domain_id = var.domain_id
  value     = "@"
  type      = "CNAME"
  name      = "www"
}

module "database" {
  source     = "../../modules/database"
  project_id = var.project_id
}

resource "random_password" "redis_password" {
  length           = 16
  special          = true
  override_special = "!#$%&*()-_=+[]{}<>:?"
}

resource "random_bytes" "app_key" {
  length = 32
}
