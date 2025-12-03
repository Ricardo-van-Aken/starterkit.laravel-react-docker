module "bucket" {
  source     = "../../modules/bucket"
  project_id = var.project_id
}

module "droplet" {
  source       = "../../modules/droplet"
  project_id   = var.project_id
  ssh_key_name = var.ssh_key_name
}

# module "database" {
#   source     = "../../modules/database"
#   project_id = var.project_id
# }

module "domain" {
  source       = "../../modules/domain"
  droplet_ipv4 = module.droplet.ipv4
  project_id   = var.project_id
}
