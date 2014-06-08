<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

/**
 * Class Vortex_Service_Serializable
 * Serializability of a class to database is enabled by the class implementing this one.
 * Classes that do not implement this interface will not have any of their state serialized or deserialized.
 * The serialization interface has no methods or fields and serves only to identify the semantics of being serializable.
 */
interface Vortex_Service_ISerializable {}