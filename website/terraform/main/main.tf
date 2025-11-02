module "project" {
  source = "./modules/project"
}

module "bucket" {
  source     = "./modules/bucket"
  project_id = module.project.id
  depends_on = [module.project]
}

module "droplet" {
  source       = "./modules/droplet"
  project_id   = module.project.id
  ssh_key_name = var.ssh_key_name
  depends_on   = [module.project]
}

module "database" {
  source     = "./modules/database"
  project_id = module.project.id
  depends_on = [module.project]
}
