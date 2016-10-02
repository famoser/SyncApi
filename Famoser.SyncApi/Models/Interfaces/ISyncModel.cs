using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Models.Interfaces
{
    /// <summary>
    /// implement this interface in your models to be synced.
    /// Mark all properties to be synced with the EntityMap attribute
    /// </summary>
    public interface ISyncModel
    {
        Guid GetId();
        void SetId(Guid id);

        /// <summary>
        /// Get unique identifier for this type for the API
        /// </summary>
        /// <returns></returns>
        string GetUniqeIdentifier();

        /// <summary>
        /// Get group identifier for this type
        /// </summary>
        /// <returns></returns>
        string GetGroupIdentifier();
    }
}
