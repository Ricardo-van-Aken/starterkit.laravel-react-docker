module "name_generator" {
  source = "../name_generator"
  name   = var.name
}

locals {
  name = "s${module.name_generator.result}"
}
