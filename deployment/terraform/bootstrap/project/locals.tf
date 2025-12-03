module "name_generator" {
  source = "../modules/name_generator"
  name   = var.name
}

locals {
  name = "${var.name}-${module.name_generator.suffix}"
}
