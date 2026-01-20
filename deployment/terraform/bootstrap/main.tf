module "project" {
  source      = "../modules/project"
  name        = var.project_name
  environment = var.project_environment
  description = var.project_description
}

module "domain" {
  source     = "../modules/domain"
  project_id = module.project.id
  name       = var.domain_name
}
