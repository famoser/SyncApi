using System;

namespace Famoser.SyncApi.Models.Interfaces.Base
{
    public interface IBaseSyncModel
    {
        /// <summary>
        /// Set a unique id.
        /// this is used by the library to keep track of which object relates to which api data
        /// </summary>
        /// <param name="id"></param>
        void SetId(Guid id);

        /// <summary>
        /// Get the Id set by SetId before
        /// </summary>
        /// <returns></returns>
        Guid GetId();
    }
}
