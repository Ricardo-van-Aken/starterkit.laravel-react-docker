module "name_generator" {
  source = "../name_generator"
  name   = var.name
}

locals {
  name = module.name_generator.result
}
