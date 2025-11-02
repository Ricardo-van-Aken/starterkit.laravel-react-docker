module "cluster_name_generator" {
  source = "../../../shared/name_generator"
  name   = var.cluster_name
}

module "db_name_generator" {
  source = "../../../shared/name_generator"
  name   = var.database_name
}

module "user_name_generator" {
  source = "../../../shared/name_generator"
  name   = var.user_name
}

locals {
  cluster_name  = module.cluster_name_generator.result
  database_name = module.db_name_generator.result
  user_name     = module.user_name_generator.result
}
