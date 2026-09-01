<?php

namespace Source\Controllers\Operation;

/**
 * Transitional HTTP adapter for operational tickets.
 *
 * ARC-002 will move the shared ticket workflow out of Studio. Keeping this
 * adapter explicit prevents the Operation controller from inheriting CMS
 * actions while preserving the operational Chamados URLs.
 */
final class ServiceDesk extends \Source\Controllers\Studio\Studio
{
}
