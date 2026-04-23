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

module "record_www" {
  source    = "../../modules/record"
  domain_id = var.domain_id
  value     = module.droplet.ipv4
  type      = "A"
  name      = var.record_name == "@" ? "www" : "www.${var.record_name}"
}

module "database" {
  source     = "../../modules/database"
  project_id = var.project_id
}
